<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Card;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    /**
     * GET /api/my-cards
     * Muestra saldo y límite (Historia #3)
     */
    public function index()
    {
        // Obtenemos el usuario autenticado
        $user = Auth::user();

        // Cargamos tarjetas con la información de su cuenta (saldo)
        // Si el método 'cards' no existe en User, consultamos Card filtrando por accounts del usuario
        $cards = Card::whereHas('account', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('account:id,balance,currency')->get();

        // Transformamos la respuesta para el Frontend
        return response()->json([
            'status' => 'success',
            'data' => $cards->map(function($card) {
                return [
                    'id' => $card->id,
                    'last_four' => $card->last_four,
                    'brand' => $card->brand,
                    'expiration' => $card->expiration_date->format('m/y'),
                    'credit_limit' => $card->credit_limit,
                    'available_balance' => $card->account->balance, // Saldo en cuenta
                    'currency' => $card->account->currency,
                    'status' => $card->status,
                ];
            })
        ]);
    }

    /**
     * POST /api/cards/request
     * Solicitar nueva tarjeta (Historia #1 y #2)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Crear una cuenta nueva para la tarjeta (o usar existente)
        $account = Account::create([
            'user_id' => $user->id,
            'account_number' => 'ACC-' . uniqid(),
            'balance' => 0.00, // Empieza en 0
        ]);

        // 2. Generar Tarjeta con prefijo 05 (Lógica manual para el controlador)
        $prefix = '05';
        $randomPart =  str_pad(mt_rand(0, 99999999999999), 14, '0', STR_PAD_LEFT);
        $pan = $prefix . $randomPart;

        $card = Card::create([
            'account_id' => $account->id,
            'card_number' => $pan, // Se encripta automágicamente
            'cvv' => rand(100, 999), // Se encripta automágicamente
            'bin' => substr($pan, 0, 6),
            'last_four' => substr($pan, -4),
            'expiration_date' => now()->addYears(4),
            'credit_limit' => 2000.00, // Límite por defecto
            'status' => 'active'
        ]);

        return response()->json([
            'message' => 'Tarjeta emitida exitosamente',
            'card_preview' => [
                'last_four' => $card->last_four,
                'limit' => $card->credit_limit
            ]
        ], 201);
    }

    /**
     * GET /api/demo/user/{id}/transactions
     * Muestra las transacciones generadas por los factories para un usuario especifico
     */
    public function demoTransactions($id)
    {
        $user = \App\Models\User::findOrFail($id);

        // Eager Loading: Traer Cuentas -> Tarjetas -> Transacciones
        $data = $user->accounts()->with(['cards.transactions'])->get();

        return response()->json([
            'client' => $user->name,
            'email' => $user->email,
            'summary' => 'Datos generados dinámicamente vía Factory',
            'accounts' => $data->map(function($account) {
                return [
                    'account_number' => $account->account_number,
                    'balance' => $account->balance,
                    'cards' => $account->cards->map(function($card) {
                        return [
                            'pan_masked' => '**** **** **** ' . $card->last_four,
                            'transactions' => $card->transactions->map(function($tx) {
                                return [
                                    'merchant' => $tx->merchant_name,
                                    'amount' => $tx->amount,
                                    'date' => $tx->created_at->format('Y-m-d H:i'),
                                    'status' => $tx->status
                                ];
                            })
                        ];
                    })
                ];
            })
        ]);
    }
}
