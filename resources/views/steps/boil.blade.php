<div>
    <div class="gap-y-10 m-4 flex flex-col justify-between items-center">
        <div class="flex w-full justify-end">
            <x-note-button />
        </div>
        <div class="flex items-center text-lg gap-x-2 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M19 5c-1.11 0-2 .89-2 2v6.76c-.64.57-1 1.39-1 2.24c0 1.66 1.34 3 3 3s3-1.34 3-3c0-.85-.36-1.67-1-2.23V7c0-1.11-.89-2-2-2m0 1c.55 0 1 .45 1 1v1h-2V7c0-.55.45-1 1-1M8 3.54l-.75.84S5.97 5.83 4.68 7.71S2 11.84 2 14c0 3.31 2.69 6 6 6s6-2.69 6-6c0-2.16-1.39-4.41-2.68-6.29S8.75 4.38 8.75 4.38L8 3.54m0 3.13c.44.52.84.95 1.68 2.17C10.89 10.6 12 12.84 12 14c0 2.22-1.78 4-4 4s-4-1.78-4-4c0-1.16 1.11-3.4 2.32-5.16C7.16 7.62 7.56 7.19 8 6.67Z" />
            </svg>
            <span class="border-b-old-gold border-0 border-b-2 text-3xl font-bold w-1/5">Boil</span>
        </div>
        <div class="flex flex-col items-center justify-center shadow-md rounded gap-y-6 bg-gray-100 px-4 w-full">
            <div class="flex justify-center gap-x-8 items-center py-2 w-full">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                    <path fill="currentColor"
                        d="M9 3V1h6v2H9Zm2 11h2V8h-2v6Zm1 8q-1.85 0-3.488-.713T5.65 19.35q-1.225-1.225-1.938-2.863T3 13q0-1.85.713-3.488T5.65 6.65q1.225-1.225 2.863-1.938T12 4q1.55 0 2.975.5t2.675 1.45l1.4-1.4l1.4 1.4l-1.4 1.4Q20 8.6 20.5 10.025T21 13q0 1.85-.713 3.488T18.35 19.35q-1.225 1.225-2.863 1.938T12 22Zm0-2q2.9 0 4.95-2.05T19 13q0-2.9-2.05-4.95T12 6Q9.1 6 7.05 8.05T5 13q0 2.9 2.05 4.95T12 20Zm0-7Z" />
                </svg>
                <div class="flex text-xl font-bold text-black mr-4">Chrono</div>
            </div>
            @if ($this->brewing->boil_start != null)
                @if (!$this->endTime())
                <span wire:poll.s>
                    il reste
                    @if (now()->diffInMinutes(Carbon\Carbon::create($this->brewing->boil_start)->addMinutes($this->brewing->boil_time)) < 1)
                        {{ now()->diffInSeconds(Carbon\Carbon::create($this->brewing->boil_start)->addMinutes($this->brewing->boil_time)) }}
                        seconde(s)
                    @else
                        {{ now()->diffInMinutes(Carbon\Carbon::create($this->brewing->boil_start)->addMinutes($this->brewing->boil_time)) }}
                        minute(s)
                    @endif
                </span>

                @else
                    <div class="flex justify-center font-semibold text-tawny pb-2 animate-pulse">Temps écoulé</div>
                @endif
            @else
                <div class="flex justify-around py-2 w-full">
                    <button
                        class="rounded px-10 py-2 font-semibold text-black bg-xanthous shadow-md"
                        wire:click="startChrono">
                        Start
                    </button>
                    <button
                        class="rounded px-10 py-2 font-semibold text-black bg-xanthous shadow-md"
                        wire:click="openModal">
                        Modifier
                    </button>
                </div>
            @endif
            @if ($isOpen)
                <div class="flex justify-center gap-4 pb-4">
                    <input class="w-1/3 text-black" type="number" wire:model="newBoilTime" placeholder="{{ $brewing->boil_time}}" value="{{ $brewing->boil_time}}">
                    <button class="p-2 bg-xanthous rounded active:bg-tawny"
                        wire:click="modifyBoilTime({{ $brewing->id }})">Change Time</button>
                </div>
            @endif
        </div>
        <div class="flex flex-col w-full gap-2">
            @foreach ($steps as $step)
                @php
                    $time_left = now()->diffInMinutes(
                        Carbon\Carbon::create($this->brewing->boil_start)
                            ->addMinutes($this->brewing->boil_time)
                            ->subMinutes($step->time),
                        false,
                    );
                @endphp
                <div class="rounded-md border-2 bg-white {{ $step->status ? 'border-old-gold' : ($this->brewing->boil_start != null && $time_left <= 0 ? 'border-red-600' : 'border-transparent') }}"
                    wire:click="statusChange({{ $step }})">
                    <div class="rounded-tl-md rounded-tr-md relative flex cursor-pointer p-4 focus:outline-none">
                        <div class="ml-3 w-full flex gap-x-1 justify-between">
                            <div class="flex gap-x-1">
                                <div class="block text-sm font-medium">
                                    {{ $step->quantity }}
                                </div>
                                <div class="block text-sm">
                                    {{ $step->unit }}
                                </div>
                                <div class="block text-sm">
                                    {{ $step->field }}
                                </div>
                            </div>
                            <div class="block text-sm">
                                @if ($this->brewing->boil_start != null && $time_left <= 0 && !$step->status)
                                    en retard batard
                                @elseif ($this->brewing->boil_start != null && $time_left > 0)
                                    dans {{ $time_left }} minutes
                                @endif
                            </div>
                        </div>
                        @if ($time_left <= 0 && !$step->status)
                            <span class="flex absolute h-3 w-3 top-0 right-0 -mt-1 -mr-1">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        {{-- Si tout est check alors on acive le bouton next, sinon il reste grise --}}
        @if ($this->allChecked && $this->endTime() && ($this->brewing->boil_start != null))
            <button type="button" wire:click="next"
                class="bg-xanthous rounded hover:bg-tawny hover:text-white transition-all duration-300 py-4 flex items-center shrink-0 w-full justify-between font-semibold uppercase">
                <div></div>
                <div class="ml-16">Next step</div>
                <svg class="mr-16"xmlns="http://www.w3.org/2000/svg" width="30" height="30"
                    viewBox="0 0 100 100">
                    <path fill="currentColor"
                        d="m50.868 78.016l36.418-26.055a2.516 2.516 0 0 0 1.051-2.043v-.006a2.52 2.52 0 0 0-1.059-2.048L50.86 21.977a2.513 2.513 0 0 0-2.612-.183a2.509 2.509 0 0 0-1.361 2.236v12.183l-32.709-.001a2.514 2.514 0 0 0-2.515 2.516l.001 22.541a2.515 2.515 0 0 0 2.516 2.516h32.706v12.187c0 .94.53 1.803 1.366 2.237a2.512 2.512 0 0 0 2.616-.193z" />
                </svg>
            </button>
        @else
            <button type="button"
                class="bg-gray-100 rounded py-4 flex items-center w-full justify-center font-semibold uppercase">
                Next step
            </button>
        @endif
    </div>
</div>
