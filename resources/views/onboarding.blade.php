<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - BancObsidiana</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

<div class="isolate bg-gray-900 px-6 py-24 sm:py-32 lg:px-8 min-h-screen">
    {{-- Fondo Decorativo (Igual que antes) --}}
    <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
        <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-1/2 -z-10 aspect-1155/678 w-144.5 max-w-none -translate-x-1/2 rotate-30 bg-linear-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%-40rem)] sm:w-288.75"></div>
    </div>

    <div class="mx-auto max-w-2xl text-center">
        <h2 class="text-4xl font-semibold tracking-tight text-balance text-white sm:text-5xl">
            @if(isset($user))
                Hola, {{ explode(' ', $user->name)[0] }}
            @elseif($type === 'business')
                Cuenta Jurídica
            @else
                Únete a BancObsidiana
            @endif
        </h2>
        <p class="mt-2 text-lg/8 text-gray-400">
            @if(isset($user))
                Completa los datos para solicitar una
                <span class="text-indigo-400 font-bold">Nueva Tarjeta {{ $type === 'business' ? 'Comercial' : 'Personal' }}</span>
            @else
                @if($type === 'business')
                    Registro exclusivo para comercios.
                @else
                    Generación instantánea de tarjetas personales.
                @endif
            @endif
        </p>

        {{-- Switcher --}}
        <div class="mt-6 flex justify-center gap-4">
            <a href="{{ route('onboarding.form', ['type' => 'personal']) }}"
               class="text-sm font-medium px-3 py-1 rounded-full transition {{ $type === 'personal' ? 'bg-indigo-500 text-white' : 'text-gray-400 hover:text-white bg-white/5' }}">
               Personal
            </a>
            <a href="{{ route('onboarding.form', ['type' => 'business']) }}"
               class="text-sm font-medium px-3 py-1 rounded-full transition {{ $type === 'business' ? 'bg-indigo-500 text-white' : 'text-gray-400 hover:text-white bg-white/5' }}">
               Comercio
            </a>
        </div>
    </div>

    <form action="{{ route('onboarding.process') }}" method="POST" class="mx-auto mt-16 max-w-xl sm:mt-20">
        @csrf
        <input type="hidden" name="account_type" value="{{ $type }}">

        <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">

            @if($type === 'business')
                {{-- COMERCIO --}}
                <div class="sm:col-span-2">
                    <label for="company_name" class="block text-sm/6 font-semibold text-white">Nombre de la Empresa</label>
                    <div class="mt-2.5">
                        {{-- Pre-llenado: Si ya existe usuario, usamos su nombre como base o vacío --}}
                        <input id="company_name" type="text" name="company_name"
                               value="{{ old('company_name', isset($user) ? $user->name : '') }}"
                               class="block w-full rounded-md bg-white/5 px-3.5 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 focus:outline-indigo-500" required>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="rif" class="block text-sm/6 font-semibold text-white">RIF</label>
                    <div class="mt-2.5">
                        {{-- Lógica RIF: Si usuario existe y está pidiendo comercio, ponemos '0' o su ID simulado --}}
                        <input id="rif" type="text" name="rif"
                               value="{{ old('rif', isset($user) ? 'J-0' . $user->id . '00' : '') }}"
                               placeholder="J-00000000-0" class="block w-full rounded-md bg-white/5 px-3.5 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 focus:outline-indigo-500" required>
                    </div>
                </div>

            @else
                {{-- PERSONAL --}}
                @php
                    // Lógica para separar nombre y apellido si el usuario existe
                    $firstName = '';
                    $lastName = '';
                    if(isset($user)) {
                        $parts = explode(' ', $user->name, 2);
                        $firstName = $parts[0] ?? '';
                        $lastName = $parts[1] ?? '';
                    }
                @endphp

                <div>
                    <label for="first-name" class="block text-sm/6 font-semibold text-white">Nombre</label>
                    <div class="mt-2.5">
                        <input id="first-name" type="text" name="first-name"
                               value="{{ old('first-name', $firstName) }}"
                               class="block w-full rounded-md bg-white/5 px-3.5 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 focus:outline-indigo-500" required>
                    </div>
                </div>

                <div>
                    <label for="last-name" class="block text-sm/6 font-semibold text-white">Apellido</label>
                    <div class="mt-2.5">
                        <input id="last-name" type="text" name="last-name"
                               value="{{ old('last-name', $lastName) }}"
                               class="block w-full rounded-md bg-white/5 px-3.5 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 focus:outline-indigo-500" required>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="cedula" class="block text-sm/6 font-semibold text-white">Cédula</label>
                    <div class="mt-2.5">
                        <input id="cedula" type="text" name="cedula"
                               value="{{ old('cedula') }}"
                               placeholder="V-12.345.678" class="block w-full rounded-md bg-white/5 px-3.5 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 focus:outline-indigo-500" required>
                    </div>
                </div>
            @endif

            <div class="sm:col-span-2">
                <label for="email" class="block text-sm/6 font-semibold text-white">Correo Electrónico</label>
                <div class="mt-2.5">
                    {{-- Email readonly si el usuario ya está logueado para evitar conflictos --}}
                    <input id="email" type="email" name="email"
                           value="{{ old('email', isset($user) ? $user->email : '') }}"
                           {{ isset($user) ? 'readonly' : '' }}
                           class="block w-full rounded-md {{ isset($user) ? 'bg-white/10 text-gray-300 cursor-not-allowed' : 'bg-white/5 text-white' }} px-3.5 py-2 text-base outline-1 -outline-offset-1 outline-white/10 focus:outline-indigo-500" required>
                </div>
            </div>

            {{-- Resto de campos (Teléfono, Checkbox) igual... --}}
            <div class="sm:col-span-2">
                <label for="phone-number" class="block text-sm/6 font-semibold text-white">Teléfono</label>
                <div class="mt-2.5">
                     <input id="phone-number" type="text" name="phone-number" placeholder="+58..." class="block w-full rounded-md bg-white/5 px-3.5 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 focus:outline-indigo-500">
                </div>
            </div>

             <div class="flex gap-x-4 sm:col-span-2">
                <div class="flex h-6 items-center">
                    <input id="agree-to-policies" type="checkbox" name="agree-to-policies" checked required />
                </div>
                <label class="text-sm/6 text-gray-400">Acepto los términos.</label>
            </div>
        </div>

        <div class="mt-10">
            <button type="submit" class="block w-full rounded-md bg-indigo-500 px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-xs hover:bg-indigo-400 transition-all hover:scale-[1.02]">
                @if(isset($user))
                    Solicitar Tarjeta Adicional
                @else
                    Registrar y Solicitar Tarjeta
                @endif
            </button>
        </div>
    </form>
</div>
</body>
</html>
