<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;


class PerfilController extends Controller
{

    public function index()
{
    $user = \Illuminate\Support\Facades\Auth::user();

    // Opción manual: Buscamos tarjetas que tengan una cuenta que pertenezca al usuario
    $card = \App\Models\Card::whereHas('account', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['account', 'transactions' => function($query) {
                    $query->latest();
                }])
                ->get();

    // AQUÍ ESTÁ EL TRUCO:
    return view('dashboard', [
        'user'  => $user,
        'cards' => $card,
        'card'  => $card->first() // Para compatibilidad con lógica de una sola tarjeta
    ]);
}

}
