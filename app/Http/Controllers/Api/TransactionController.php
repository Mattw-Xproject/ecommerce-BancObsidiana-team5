<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function process(Request $request)
    {
        // 1. Validación del nuevo esquema JSON
        $validator = Validator::make($request->all(), [
            'card_number'         => 'required|string',
            'cvv'                 => 'required|string',
            'expiry'              => 'required|string', // Formato MM/YY
            'amount'              => 'required|numeric|min:0.01',
            'description'         => 'nullable|string|max:255',
            'destination_account' => 'nullable|string', // Opcional
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'ERROR', 'errors' => $validator->errors()], 422);
        }

        $cardNumber = str_replace(' ', '', $request->card_number); // Limpiamos espacios
        $bin = substr($cardNumber, 0, 2);

        // 2. Lógica de Adquirencia (Routing)
        if ($bin !== '05') {
            return response()->json([
                'status' => 'ROUTING',
                'message' => 'Tarjeta ajena a BancObsidiana. Redirigiendo al emisor...',
                'external_bin' => $bin
            ], 200);
        }

        // 3. PROCESAMIENTO LOCAL (Atomicidad total)
        DB::beginTransaction();
        try {
            // Buscamos la tarjeta asegurando que traiga la cuenta y el dueño (User)
            $card = Card::where('card_number', $cardNumber)
                        ->with(['account.user'])
                        ->first();

            // 1. Limpieza y extracción de BIN
            $cardNumberInput = str_replace(' ', '', $request->card_number);
            $bin = substr($cardNumberInput, 0, 2); // Tomamos los primeros 2 dígitos

            // 2. Lógica de Adquirencia (Routing)
            // Si no empieza por 05, ni siquiera buscamos en nuestra base de datos
            if ($bin !== '05') {
                return response()->json([
                    'status' => 'ROUTING',
                    'message' => 'Tarjeta ajena a BancObsidiana. Redirigiendo al emisor...',
                    'external_bin' => $bin,
                    'action' => 'redirect_to_external_gateway'
                ], 200);
            }

            // 3. Si llega aquí, es "nuestra" (05). Procedemos a buscarla:
            $card = Card::where('card_number', $cardNumberInput)
                        ->with('account.user')
                        ->first();

            if (!$card) {
                return response()->json(['status' => 'DECLINED', 'message' => 'Tarjeta 05 inexistente'], 404);
            }
            // Validar CVV
            if ($card->cvv !== $request->cvv) {
                return response()->json(['status' => 'DECLINED', 'message' => 'CVV Incorrecto'], 402);
            }

            // Validar Expiración (MM/YY)
            $expiryInput = $request->expiry; // Ej: "01/31"
            if ($card->expiration_date->format('m/y') !== $expiryInput) {
                return response()->json(['status' => 'DECLINED', 'message' => 'Fecha de expiración no coincide'], 402);
            }

            $amount = $request->amount;
            $fee = round($amount * 0.02, 2); // 2% Comisión
            $totalToDebit = $amount + $fee;

            // Validar Saldo del Usuario
            if ($card->account->balance < $totalToDebit) {
                return response()->json(['status' => 'DECLINED', 'message' => 'Saldo insuficiente para monto + comisión'], 402);
            }

            // --- OPERACIÓN BANCARIA ---

            // A. Descontar al Emisor
            $card->account->decrement('balance', $totalToDebit);

            // B. Si hay cuenta destino, acreditar (Transferencia interna)
            $destAccount = null;
            if ($request->has('destination_account')) {
                $destAccount = Account::where('account_number', $request->destination_account)->first();
                if ($destAccount) {
                    $destAccount->increment('balance', $amount);
                }
            }

            // C. Registrar Transacción en el historial
            $transaction = Transaction::create([
                'card_id'          => $card->id,
                'account_id'       => $card->account->id,
                'merchant_name'    => $request->description ?? 'Compra Online',
                'reference'        => 'OBS-' . strtoupper(bin2hex(random_bytes(4))),
                'type'             => $destAccount ? 'TRANSFER' : 'PURCHASE',
                'amount'           => $amount,
                'fee'              => $fee,
                'status'           => 'approved',
                'response_message' => "Pago a: " . ($request->destination_account ?? "Comercio Externo")
            ]);

            DB::commit();

            return response()->json([
                'status' => 'APPROVED',
                'auth'   => $transaction->reference,
                'client' => $card->account->user->name, // Devolvemos el nombre del dueño
                'details' => [
                    'debited' => $totalToDebit,
                    'fee' => $fee,
                    'description' => $transaction->merchant_name
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'ERROR', 'message' => $e->getMessage()], 500);
        }
    }
}
