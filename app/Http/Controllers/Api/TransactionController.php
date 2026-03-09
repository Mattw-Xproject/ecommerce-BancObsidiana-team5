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
use App\Http\Controllers\Controller;


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

        // 2. Limpieza y extracción de BIN
        $cardNumberInput = str_replace(' ', '', $request->card_number);
        $bin2 = substr($cardNumberInput, 0, 2); // Para validar BancObsidiana (05)
        $bin6 = substr($cardNumberInput, 0, 6); // Para validar bancos externos (ej. 465100)

        // 3. Lógica de Adquirencia (Routing)
        if ($bin2 !== '05') {
            
            // Verificamos si la tarjeta pertenece al banco externo (Equipo 5) según la documentación
            if ($bin6 === '465100' || $bin2 === '46') {
                try {
                    // Petición al endpoint del otro banco simulando el JSON requerido
                    $response = Http::post('http://3.144.142.161/api/transactions/simulate/', [
                        'button_bank_external' => true,
                        'bank_identifier'      => 'cienspay',
                        'card_number'          => $cardNumberInput,
                        'expiry_date'          => $request->expiry, // Ajustado al nombre del campo que pide su API
                        'cvv'                  => $request->cvv,
                        'amount'               => (string) $request->amount,
                        'description'          => $request->description ?? 'Compra desde comercio',
                    ]);

                    // Devolvemos exactamente lo que responda el banco externo
                    return response()->json($response->json(), $response->status());

                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'ERROR',
                        'message' => 'Fallo al conectar con el banco emisor (cienspay)',
                        'error' => $e->getMessage()
                    ], 502); // 502 Bad Gateway
                }
            }

            // Si es de un banco ajeno que no es el Equipo 5 (y tampoco es el tuyo)
            return response()->json([
                'status' => 'ROUTING',
                'message' => 'Tarjeta ajena a BancObsidiana. Redirigiendo al emisor...',
                'external_bin' => $bin6
            ], 200);
        }

        // 4. PROCESAMIENTO LOCAL (BancObsidiana - BIN 05)
        DB::beginTransaction();
        try {
            // Buscamos la tarjeta asegurando que traiga la cuenta y el dueño (User)
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
