<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Tarjeta - BancObsidiana</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

<div class="isolate bg-gray-900 px-6 py-24 sm:py-32 lg:px-8">
   <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
    <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-1/2 -z-10 aspect-1155/678 w-144.5 max-w-none -translate-x-1/2 rotate-30 bg-linear-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%-40rem)] sm:w-288.75"></div>
  </div>

    <div class="mx-auto max-w-2xl text-center">
    <h2 class="text-4xl font-semibold tracking-tight text-balance text-white sm:text-5xl">Solicita tu Tarjeta</h2>
    <p class="mt-2 text-lg/8 text-gray-400">Unete a Obsidian Credit. Generación instantánea de tarjetas personales.</p>
  </div>

  <form action="{{ route('register.cards.process') }}" method="POST" class="mx-auto mt-16 max-w-xl sm:mt-20">
    @csrf
    <input type="hidden" name="account_type" value="personal">

    <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">

      <div>
        <label for="first-name" class="block text-sm/6 font-semibold text-white">Nombre</label>
        <div class="mt-2.5">
          <input id="first-name" type="text" name="first-name" autocomplete="given-name" class="block w-full rounded-md bg-white/5 px-3.5 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500" required>
        </div>
      </div>

      <div>
        <label for="last-name" class="block text-sm/6 font-semibold text-white">Apellido</label>
        <div class="mt-2.5">
          <input id="last-name" type="text" name="last-name" autocomplete="family-name" class="block w-full rounded-md bg-white/5 px-3.5 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500" required>
        </div>
      </div>

      {{-- CÉDULA DE IDENTIDAD (Campo nuevo para Personas) --}}
      <div class="sm:col-span-2">
        <label for="cedula" class="block text-sm/6 font-semibold text-white">Cédula de Identidad</label>
        <div class="mt-2.5">
          <input id="cedula" type="text" name="cedula" placeholder="V-12.345.678" class="block w-full rounded-md bg-white/5 px-3.5 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500" required>
        </div>
      </div>

      <div class="sm:col-span-2">
        <label for="email" class="block text-sm/6 font-semibold text-white">Correo Electrónico Personal</label>
        <div class="mt-2.5">
          <input id="email" type="email" name="email" autocomplete="email" class="block w-full rounded-md bg-white/5 px-3.5 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500" required>
        </div>
      </div>

      <div class="sm:col-span-2">
        <label for="phone-number" class="block text-sm/6 font-semibold text-white">Teléfono Móvil</label>
        <div class="mt-2.5">
          <div class="flex rounded-md bg-white/5 outline-1 -outline-offset-1 outline-white/10 has-[input:focus-within]:outline-2 has-[input:focus-within]:-outline-offset-2 has-[input:focus-within]:outline-indigo-500">
            <div class="grid shrink-0 grid-cols-1 focus-within:relative">
              <select id="country" name="country" class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-transparent py-2 pr-7 pl-3.5 text-base text-gray-400 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6">
                <option>VE</option>
                <option>US</option>
              </select>
            </div>
            <input id="phone-number" type="text" name="phone-number" placeholder="+58 (424) 000-0000" class="block min-w-0 grow bg-transparent py-1.5 pr-3 pl-1 text-base text-white placeholder:text-gray-500 focus:outline-none sm:text-sm/6">
          </div>
        </div>
      </div>

      <div class="flex gap-x-4 sm:col-span-2">
        <div class="flex h-6 items-center">
            <input id="agree-to-policies" type="checkbox" name="agree-to-policies" aria-label="Agree to policies" required />
        </div>
        <label for="agree-to-policies" class="text-sm/6 text-gray-400">
          Acepto la generación de <a href="#" class="font-semibold whitespace-nowrap text-indigo-400">tarjetas de crédito virtuales</a>.
        </label>
      </div>
    </div>

    <div class="mt-10">
      <button type="submit" class="block w-full rounded-md bg-indigo-500 px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-xs hover:bg-indigo-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
        Solicitar Tarjeta Obsidiana
      </button>
    </div>
  </form>
</div>

</body>
</html>
