<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DashboardController extends Controller
{
    /*public function index()
    {/*
        $user = Auth::user();

        // Obtenemos la primera tarjeta del usuario a través de sus cuentas
        // Cargamos 'account' para el saldo y 'transactions' para el historial
        $mainCard = $user->cards()
            ->with(['account', 'transactions' => function($query) {
                $query->latest()->limit(10); // Traer solo las últimas 10
            }])
            ->first();

        return view('dashboard', [
            'card' => $mainCard,
            'user' => $user
        ]);
    }

    // app/Http/Controllers/DashboardController.php
    */
    // DashboardController.php
    // app/Http/Controllers/DashboardController.php

public function index()
{
    $user = \Illuminate\Support\Facades\Auth::user();

    // Traemos TODAS las tarjetas vinculadas a las cuentas del usuario
    $cards = \App\Models\Card::whereHas('account', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['account', 'transactions' => function($query) {
                $query->latest(); // Últimas transacciones de cada tarjeta
            }])
            ->get(); // <--- Cambiado de first() a get()

    return view('dashboard') ->with('user', $user)->with('card', $cards);
}
}
