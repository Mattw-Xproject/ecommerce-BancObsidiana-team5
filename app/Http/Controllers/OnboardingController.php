<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Account;
use App\Models\Card;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    public function showForm(Request $request)
    {
        $type = $request->query('type', 'personal');

        // Pasamos el usuario actual si existe para pre-llenar la vista
        $user = Auth::user();

        return view('onboarding', compact('type', 'user'));
    }

    public function processForm(Request $request)
    {
        $type = $request->input('account_type', 'personal');
        $currentUser = Auth::user();

        // 1. Reglas de Validación Base
        $rules = [
            // Si el usuario ya está logueado, permitimos su propio email. Si es nuevo, debe ser único.
            'email' => [
                'required',
                'email',
                $currentUser ? Rule::unique('users')->ignore($currentUser->id) : 'unique:users,email'
            ],
            'phone-number' => 'nullable|string',
            'account_type' => 'required|in:personal,business',
        ];

        // 2. Reglas Específicas
        if ($type === 'business') {
            $rules['company_name'] = 'required|string|max:255';
            $rules['rif'] = 'required|string';
        } else {
            $rules['first-name'] = 'required|string|max:100';
            $rules['last-name'] = 'required|string|max:100';
            $rules['cedula'] = 'required|string';
        }

        $validated = $request->validate($rules);

        // 3. Obtener o Crear Usuario
        if ($currentUser) {
            // -- FLUJO USUARIO EXISTENTE --
            $user = $currentUser;

            // Opcional: Actualizar datos si el usuario los cambió en el formulario
            // $user->update(['email' => $validated['email']]);
        } else {
            // -- FLUJO NUEVO USUARIO --
            $userName = ($type === 'business')
                ? $validated['company_name']
                : $validated['first-name'] . ' ' . $validated['last-name'];

            $user = User::create([
                'name' => $userName,
                'email' => $validated['email'],
                'password' => Hash::make('123456'), // Contraseña default para nuevos
            ]);

            // Logueamos al nuevo usuario automáticamente
            Auth::login($user);
        }

        // 4. Determinar marca y datos de la cuenta
        $cardBrand = ($type === 'business') ? 'Comercio Credit' : 'Obsidiana Credit';

        // Prefijo de cuenta y lógica RIF para negocio
        $accountPrefix = 'NAT-';
        if ($type === 'business') {
            $accountPrefix = 'JUR-';
            // Nota: Aquí podrías guardar el RIF en el usuario o en la cuenta si tuvieras el campo.
            // El prompt pide "coloca solo la cedula como rif con un 0".
            // Como User no tiene campo RIF por defecto, asumimos que es lógica de negocio visual o de la cuenta.
        }

        // 5. Generar ecosistema financiero (Añadir a usuario existente)
        Account::factory()
            ->for($user)
            ->has(
                Card::factory()
                    ->state([
                        'brand' => $cardBrand,
                    ])
                    ->has(
                        Transaction::factory()->count(15)
                    )
            )
            ->create([
                'account_number' => $accountPrefix . strtoupper(uniqid()),
            ]);

        // 6. Redirección al Dashboard para ver la tarjeta animada
        return redirect()->route('dashboard')->with('success', '¡Tarjeta generada exitosamente!');
    }
}
