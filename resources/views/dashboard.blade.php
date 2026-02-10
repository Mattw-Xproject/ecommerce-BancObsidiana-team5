<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-8 rounded-xl p-4">

        {{-- Mensaje de éxito --}}
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p class="font-bold">¡Excelente!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if($cards->isEmpty())
             <div class="p-6 bg-zinc-100 border-l-4 border-zinc-400 text-black rounded-md shadow-sm">
                <h3 class="font-bold text-lg">Bienvenido a BancObsidiana</h3>
                <p>Comienza creando tu primera tarjeta.</p>
                <a href="{{ route('onboarding.form') }}" class="mt-4 inline-block bg-indigo-600 text-black dark:text-white px-4 py-2 rounded text-sm font-bold">Ir al Registro</a>
             </div>

        @else

            @foreach($cards as $card)
                <div class="space-y-6 border-b border-neutral-200 dark:border-neutral-700 pb-12 last:border-0">

                    {{-- Encabezado Tarjeta --}}
                    <div class="flex items-center gap-2">
                        <span class="bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded">Tarjeta #{{ $loop->iteration }}</span>
                        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                             {{ $card->brand }} - {{ substr($card->account->account_number, 0, 8) }}...
                        </h2>
                    </div>

                    <div class="grid auto-rows-min gap-4 md:grid-cols-3">

                        {{-- 1. TARJETA FLIP CORREGIDA --}}
                        <div id="card-container-{{ $card->id }}" class="flip-card-container relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 group cursor-pointer" style="perspective:1000px;">
                            <div id="card-inner-{{ $card->id }}" class="absolute inset-0 w-full h-full transition-transform duration-700" style="transform-style:preserve-3d;">

                                {{-- CARA FRONTAL --}}
                                <div class="absolute inset-0 w-full h-full" style="backface-visibility:hidden; transform:rotateY(0deg);">
                                    <img src="{{ $card->brand == 'Comercio Credit' ? '/front-credit-card-gold.png' : '/front-credit-card-platinium.png' }}" class="absolute inset-0 w-full h-full object-cover">
                                    <div class="absolute inset-0 flex flex-col justify-center p-6 text-white">
                                        <div class="flex justify-left items-start mt-10 pt-2 mx-2">
                                            <img src="https://pngimg.com/uploads/bank_chip/bank_chip_PNG6.png" class="w-10 h-6 opacity-90" />
                                            <span class="font-bold italic tracking-wider text-xs mx-2 uppercase">{{ $card->brand }}</span>
                                        </div>
                                        <div class="w-full text-center py-2 mt-2">
                                            <p class="text-lg font-mono tracking-widest">**** **** **** {{ substr($card->card_number, -4) }}</p>
                                        </div>
                                        <div class="flex justify-between items-end mt-auto text-[10px] uppercase">
                                            <p>{{ auth()->user()->name }}</p>
                                            <p>{{ $card->expiration_date->format('m/y') }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- CARA TRASERA (Respetando tu estructura con merge de corrección) --}}
                                <div class="absolute inset-0 w-full h-full" style="backface-visibility:hidden; transform:rotateY(180deg);">
                                    {{-- Imagen de fondo --}}
                                    <img src="/back-credit-card.png"
                                         class="absolute inset-0 w-full h-full object-cover"
                                         alt="Reverso Tarjeta"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">

                                    {{-- Fallback (Negro) --}}
                                    <div class="hidden absolute inset-0 w-full h-full bg-neutral-900"></div>

                                    {{-- Contenido Trasero (He quitado 'hidden' para que sea visible al girar y corregido el 'pointer-events') --}}
                                    <div id="back-overlay-{{ $card->id }}" class="absolute inset-0 z-10" style="transform-style:preserve-3d;">
                                        <div class="h-full flex items-center justify-between w-full px-8 pt-12">
                                            <div class="flex flex-col items-start justify-center w-2/3">
                                                <div class="w-full">
                                                    <div class="mt-4 flex items-center justify-start">
                                                        <div class="bg-white px-1 py-0.5 rounded-sm flex items-center gap-2 shadow-inner border border-gray-300">
                                                            <span class="text-[6px] font-bold text-black tracking-tighter"><sup>cvv</sup></span>
                                                            <span class="text-black font-mono font-bold text-xs tracking-widest">{{ $card->cvv }}</span>
                                                        </div>
                                                    </div>
                                                    {{-- Firma --}}
                                                    <p class="text-[6px] pt-3 text-gray-300 mt-3 text-left italic drop-shadow">
                                                        <sub>Authorized Signature</sub>
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex flex-col items-end justify-center w-1/3">
                                                {{-- QR Redimensionado: Más pequeño --}}
                                                <div class="flex flex-col items-center justify-between h-10 w-10">
                                                    <p class="text-[5px] text-gray-400 uppercase tracking-tighter mb-1">Escanea</p>
                                                    <div class="h-full w-full flex justify-center bg-white p-0.5 rounded-sm shadow-sm">
                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ $card->card_number }}"
                                                            alt="QR"
                                                            class="h-full aspect-square object-contain"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. SALDO (Letras negras en modo oscuro) --}}
                        <div class="relative aspect-video rounded-xl border border-zinc-200 p-6 flex flex-col justify-center bg-zinc-50 dark:bg-zinc-100 text-black shadow-sm">
                            <p class="text-[10px] text-zinc-500 font-bold uppercase">Saldo Disponible</p>
                            <span class="text-3xl font-bold mt-1">${{ number_format($card->account->balance, 2) }}</span>
                            <div class="mt-4 flex items-center gap-2">
                                <span class="inline-block w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                <span class="text-xs text-green-600 font-semibold">Cuenta Activa</span>
                            </div>
                            {{-- Solicitar Tarjeta Adicional (Más pequeño) --}}
                            <div class="mt-4 pt-4 border-t border-zinc-200">
                                <a href="{{ route('onboarding.form', ['type' => 'personal']) }}" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 group">
                                    <flux:icon.plus class="size-3 transition-transform group-hover:rotate-90" />
                                    Solicitar Tarjeta Adicional
                                </a>
                            </div>
                        </div>

                        {{-- 3. LÍMITE (Letras negras en modo oscuro) --}}
                        <div class="relative aspect-video rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 flex flex-col justify-center bg-white dark:bg-zinc-100 text-black">
                             <p class="text-sm font-medium uppercase text-zinc-500">Límite de Crédito</p>
                             <h2 class="text-3xl font-bold mt-2">${{ number_format($card->credit_limit, 2) }}</h2>
                             <div class="mt-4 w-full bg-zinc-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: 45%"></div>
                             </div>
                        </div>
                    </div>

                    {{-- SECCIÓN MOVIMIENTOS RECIENTES (Plegable con botón largo) --}}
                    <div class="w-full">
                        <button
                            onclick="toggleTransactions({{ $card->id }})"
                            class="w-full flex items-center justify-between px-6 py-3 bg-zinc-100 dark:bg-zinc-200 text-black rounded-lg border border-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-300 transition-all font-bold text-sm shadow-sm"
                        >
                            <span class="flex items-center gap-2 dark:text-zinc-400">
                                <flux:icon.list-bullet class="size-4" />
                                Ver Movimientos Recientes
                            </span>
                            <flux:icon.chevron-down id="icon-{{ $card->id }}" class="size-4 transition-transform duration-300" />
                        </button>

                        <div id="transactions-{{ $card->id }}" class="hidden overflow-hidden transition-all duration-300 mt-2 rounded-xl border border-zinc-200 bg-white dark:bg-zinc-100 text-black shadow-sm">
                            <div class="p-4 overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead class="text-zinc-500 uppercase text-[9px] border-b border-zinc-200">
                                        <tr>
                                            <th class="py-3 px-4">Comercio</th>
                                            <th class="py-3 px-4 text-right">Fecha</th>
                                            <th class="py-3 px-4 text-center">Estado</th>
                                            <th class="py-3 px-4 text-right">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100">
                                        @forelse($card->transactions as $tx)
                                            <tr class="hover:bg-zinc-50/50 transition-colors">
                                                {{-- Comercio y Referencia --}}
                                                <td class="py-3 px-4">
                                                    <div class="font-bold text-xs text-black">{{ $tx->merchant_name }}</div>
                                                    <div class="text-[8px] text-zinc-400 font-mono tracking-tighter uppercase">{{ $tx->reference }}</div>
                                                </td>

                                                {{-- Fecha --}}
                                                <td class="py-3 px-4 text-right text-[10px] text-zinc-600">
                                                    {{ $tx->created_at->format('d M, Y') }}
                                                </td>

                                                {{-- Estado Dinámico --}}
                                                <td class="py-3 px-4 text-center">
                                                    @php
                                                        $statusStyles = match($tx->status) {
                                                            'approved' => 'bg-green-50 text-green-600 border-green-200',
                                                            'declined' => 'bg-red-50 text-red-600 border-red-200',
                                                            'routing'  => 'bg-blue-50 text-blue-600 border-blue-200',
                                                            default    => 'bg-zinc-50 text-zinc-500 border-zinc-200',
                                                        };
                                                    @endphp
                                                    <span class="px-2 py-0.5 text-[9px] font-black rounded-full border {{ $statusStyles }}">
                                                        {{ strtoupper($tx->status) }}
                                                    </span>
                                                </td>

                                                {{-- Monto con Fee --}}
                                                <td class="py-3 px-4 text-right">
                                                    <div class="font-mono font-bold text-xs text-black">
                                                        ${{ number_format($tx->amount, 2) }}
                                                    </div>
                                                    @if($tx->fee > 0)
                                                        <div class="text-[9px] text-zinc-400 font-medium leading-none" title="Comisión bancaria del 2%">
                                                            +${{ number_format($tx->fee, 2) }} <span class="text-[8px]">fee</span>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-8 text-center text-zinc-400 text-xs italic">
                                                    No hay movimientos registrados en esta tarjeta.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            @endforeach
        @endif
    </div>

    {{-- SCRIPTS --}}
    <script>
        // Función para colapsar movimientos
        function toggleTransactions(id) {
            const container = document.getElementById(`transactions-${id}`);
            const icon = document.getElementById(`icon-${id}`);

            if (container.classList.contains('hidden')) {
                container.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                container.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // Función para el Flip de la tarjeta (Sin voltear contenido)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.flip-card-container').forEach(container => {
                const id = container.id.split('-').pop();
                const inner = document.getElementById(`card-inner-${id}`);
                let flipped = false;

                container.addEventListener('click', () => {
                    flipped = !flipped;
                    inner.style.transform = flipped ? 'rotateY(180deg)' : 'rotateY(0deg)';
                });
            });
        });
    </script>
</x-layouts.app>
