<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http; // <-- IMPORTANTE: Importamos el cliente HTTP
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function process(Request $request)
    {
        // 1. Validación del esquema JSON que recibe nuestro banco desde el comercio
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

        // Limpieza y extracción de BIN
        $cardNumberClean = str_replace(' ', '', $request->card_number); // Sin espacios para uso local
        $bin = substr($cardNumberClean, 0, 2);

        // -------------------------------------------------------------------
        // 2. LÓGICA DE ADQUIRENCIA (ENRUTAMIENTO A OTRO BANCO)
        // -------------------------------------------------------------------
        if ($bin !== '05') {

            // Construimos el JSON exactamente como lo pide la API externa
            $externalPayload = [
                'amount'               => (string) $request->amount, // Lo convertimos a string según tu ejemplo
                'bank_identifier'      => 'bancobsidiana', // Nos identificamos ante ellos
                'button_bank_external' => true,
                'card_number'          => $request->card_number, // Enviamos el original (con o sin espacios)
                'cvv'                  => $request->cvv,
                'description'          => $request->description ?? 'Compra en Tienda Demo',
                'expiry_date'          => $request->expiry // Mapeamos de 'expiry' a 'expiry_date'
            ];

            try {
                // Realizamos la petición POST al banco externo (Cienspay - Equipo 5)
                $response = Http::timeout(15)
                                ->withHeaders(['Content-Type' => 'application/json'])
                                ->post('http://3.144.142.161/api/transactions/simulate/', $externalPayload);

                // Reenviamos la respuesta del banco externo a nuestro comercio
                return response()->json([
                    'status' => 'ROUTED',
                    'message' => 'Transacción procesada por banco externo.',
                    'external_bank_response' => $response->json(), // Respuesta decodificada de ellos
                    'http_code' => $response->status()
                ], $response->status());

            } catch (\Exception $e) {
                // Si el servidor del otro banco está caído o tarda mucho
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'Error de conexión con el banco emisor externo.',
                    'error_detail' => $e->getMessage()
                ], 502); // 502 Bad Gateway
            }
        }

        // -------------------------------------------------------------------
        // 3. PROCESAMIENTO LOCAL (BancObsidiana - BIN 05)
        // -------------------------------------------------------------------
        DB::beginTransaction();
        try {
            // Buscamos la tarjeta asegurando que traiga la cuenta y el dueño (User)
            $card = Card::where('card_number', $cardNumberClean)
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
            $expiryInput = $request->expiry;
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

            // --- OPERACIÓN BANCARIA LOCAL ---

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

            // C. Registrar Transacción en el historial local
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
                'status'  => 'APPROVED',
                'auth'    => $transaction->reference,
                'client'  => $card->account->user->name,
                'details' => [
                    'debited'     => $totalToDebit,
                    'fee'         => $fee,
                    'description' => $transaction->merchant_name
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'ERROR', 'message' => $e->getMessage()], 500);
        }
    }
}
