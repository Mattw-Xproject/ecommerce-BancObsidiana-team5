<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http; // Para la redirección externa

class TransactionController extends Controller
{
    public function process(Request $request)
    {
        // 1. Validación de los datos de entrada
        $validator = Validator::make($request->all(), [
            'card_number'         => 'required|string',
            'cvv'                 => 'required|string',
            'expiry'              => 'required|string', // Se recibe como MM/YY
            'amount'              => 'required|numeric|min:0.01',
            'description'         => 'nullable|string|max:255',
            'destination_account' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'ERROR', 'errors' => $validator->errors()], 422);
        }

        // 2. Preparación de datos y detección de BIN
        $cardNumber = str_replace(' ', '', $request->card_number);
        $bin2 = substr($cardNumber, 0, 2); // Para BancObsidiana (05)
        $bin6 = substr($cardNumber, 0, 6); // Para Banco Externo (465100) 

        // 3. LÓGICA DE ADQUIRENCIA (Redirección a Banco Externo)
        // Si no es nuestra tarjeta (05), verificamos si es del Equipo 5
        if ($bin2 !== '05') {
            if ($bin6 === '465100' || substr($bin2, 0, 2) === '46') {
                try {
                    $response = Http::post('http://3.144.142.161/api/transactions/simulate/', [
                        'button_bank_external' => true, [cite: 1]
                        'bank_identifier'      => 'cienspay', [cite: 1]
                        'card_number'          => $cardNumber, [cite: 1]
                        'expiry_date'          => $request->expiry, // Mapeado al formato que ellos piden 
                        'cvv'                  => $request->cvv, [cite: 1]
                        'amount'               => (string) $request->amount, [cite: 1]
                        'description'          => $request->description ?? 'Compra desde comercio', [cite: 1]
                    ]);

                    return response()->json($response->json(), $response->status());
                } catch (\Exception $e) {
                    return response()->json(['status' => 'ERROR', 'message' => 'Error de conexión con banco externo'], 502);
                }
            }

            return response()->json([
                'status' => 'ROUTING',
                'message' => 'Tarjeta ajena. Redirigiendo...',
                'external_bin' => $bin2
            ], 200);
        }

        // 4. PROCESAMIENTO LOCAL (BancObsidiana - BIN 05)
        DB::beginTransaction();
        try {
            $card = Card::where('card_number', $cardNumber)->with('account.user')->first();

            if (!$card) {
                return response()->json(['status' => 'DECLINED', 'message' => 'Tarjeta no encontrada'], 404);
            }

            // Validaciones de seguridad local
            if ($card->cvv !== $request->cvv) {
                return response()->json(['status' => 'DECLINED', 'message' => 'CVV Incorrecto'], 402);
            }

            if ($card->expiration_date->format('m/y') !== $request->expiry) {
                return response()->json(['status' => 'DECLINED', 'message' => 'Fecha de expiración inválida'], 402);
            }

            $totalToDebit = $request->amount + round($request->amount * 0.02, 2);

            if ($card->account->balance < $totalToDebit) {
                return response()->json(['status' => 'DECLINED', 'message' => 'Saldo insuficiente'], 402);
            }

            // Ejecución de la transacción
            $card->account->decrement('balance', $totalToDebit);

            $transaction = Transaction::create([
                'card_id'    => $card->id,
                'account_id' => $card->account->id,
                'amount'     => $request->amount,
                'reference'  => 'OBS-' . strtoupper(bin2hex(random_bytes(4))),
                'status'     => 'approved',
                'merchant_name' => $request->description ?? 'Compra Online'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'APPROVED',
                'auth'   => $transaction->reference,
                'client' => $card->account->user->name
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'ERROR', 'message' => $e->getMessage()], 500);
        }
    }
}
