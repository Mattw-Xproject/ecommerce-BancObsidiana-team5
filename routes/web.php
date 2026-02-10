<?php

use App\Http\Controllers\ReleaseController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PerfilController;
use Livewire\Attributes\On;

// Cambiamos Route::view por Route::get para que use el controlador
Route::get('dashboard', [PerfilController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('home', function () {
    return view('home');
})->name('home');

Route::get('/', function () {
    return view('welcome');
})->name('home');

// 1. Ruta para mostrar el formulario unificado
// Esta ruta acepta el parámetro opcional ?type=personal o ?type=business
Route::get('/registro-cliente', [OnboardingController::class, 'showForm'])
    ->name('onboarding.form');

// 2. Ruta para procesar el envío del formulario
// Es la que recibe los datos, ejecuta los factories y devuelve el JSON
Route::post('/registro-cliente', [OnboardingController::class, 'processForm'])
    ->name('onboarding.process');




// Ruta para ver el formulario

//Route::get('/registro-cliente', [OnboardingController::class, 'showForm'])->name('onboarding.form');



// Ruta para procesar el formulario y generar los datos con factories

//Route::post('/registro-cliente', [OnboardingController::class, 'processForm'])->name('onboarding.process');


// 3. Ruta de la API Demo (Referencia para que el link que devuelve el JSON funcione)
// Asegúrate de tener esta lógica en un controlador de API o aquí mismo para la demo

Route::get('/api/demo/user/{user}/transactions', function (\App\Models\User $user) {
    // Retornamos el JSON con toda la relación cargada
    return response()->json($user->load('accounts.cards.transactions'));
})->name('api.demo.transactions');

Route::get('/user/{name}/{id?}/', function ($name, $id = null) {
    return "This is user {$name} with ID {$id}";
})->where('id', '[0-9]+')->name('user.show');

Route::get('/about', [ReleaseController::class, 'index'])->name('about.index');

/*Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');*/

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
