<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Account;
use App\Models\Card;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;


class PerfilController extends Controller
{

    public function showForm(Request $request)
    {
        // Detectamos el tipo desde la URL (?type=business o ?type=personal)
        $type = $request->query('type', 'personal');
        return view('onboarding', compact('type'));
    }

    public function processForm(Request $request)
    {
        $type = $request->input('account_type', 'personal');

        // 1. Validación Dinámica
        $rules = [
            'email' => 'required|email|unique:users,email',
            'phone-number' => 'nullable|string',
            'account_type' => 'required|in:personal,business',
        ];

        if ($type === 'business') {
            $rules['company_name'] = 'required|string|max:255';
            $rules['rif'] = 'required|string';
        } else {
            $rules['first-name'] = 'required|string|max:100';
            $rules['last-name'] = 'required|string|max:100';
            $rules['cedula'] = 'required|string';
        }

        $validated = $request->validate($rules);

        // 2. Definir el nombre del Usuario según el tipo
        $userName = ($type === 'business')
            ? $validated['company_name']
            : $validated['first-name'] . ' ' . $validated['last-name'];

        // 3. Crear el Usuario
        $user = User::create([
            'name' => $userName,
            'email' => $validated['email'],
            'password' => Hash::make('password123'),
        ]);

        // 4. Determinar la marca de la tarjeta
        $cardBrand = ($type === 'business') ? 'Comercio Credit' : 'Obsidian Credit';

        // 5. Generar ecosistema financiero con Factories
        Account::factory()
            ->for($user)
            ->has(
                Card::factory()
                    ->state([
                        'brand' => $cardBrand, // Forzamos la marca según el tipo de registro
                    ])
                    ->has(
                        Transaction::factory()->count(15)
                    )
            )
            ->create([
                'account_number' => ($type === 'business' ? 'JUR-' : 'NAT-') . strtoupper(uniqid()),
            ]);

        // 6. Respuesta
        $apiLink = url("/api/demo/user/{$user->id}/transactions");

        return response()->json([
            'status' => 'success',
            'message' => 'Identidad digital y tarjetas generadas exitosamente.',
            'type_registered' => $type,
            'brand_assigned' => $cardBrand,
            'user_created' => $user->name,
            'api_json_link' => $apiLink,
            'next_steps' => 'Ahora puede ver estos datos en su dashboard simulado.'
        ]);
    }

}
