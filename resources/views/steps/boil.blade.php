<div class="pb-20">

    @include('steps._header', ['recipe' => $this->recipe, 'brewing' => $this->brewing])

    {{-- Chrono --}}
    <div class="sticky top-20 z-10 shadow-lg flex flex-col items-center justify-center bg-rich-black px-4 py-6 w-full">
        <div class="flex justify-center gap-x-2 items-center py-2 text-gray-100">
            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor" d="M9 3V1h6v2H9Zm2 11h2V8h-2v6Zm1 8q-1.85 0-3.488-.713T5.65 19.35q-1.225-1.225-1.938-2.863T3 13q0-1.85.713-3.488T5.65 6.65q1.225-1.225 2.863-1.938T12 4q1.55 0 2.975.5t2.675 1.45l1.4-1.4l1.4 1.4l-1.4 1.4Q20 8.6 20.5 10.025T21 13q0 1.85-.713 3.488T18.35 19.35q-1.225 1.225-2.863 1.938T12 22Zm0-2q2.9 0 4.95-2.05T19 13q0-2.9-2.05-4.95T12 6Q9.1 6 7.05 8.05T5 13q0 2.9 2.05 4.95T12 20Zm0-7Z" />
            </svg>
            <div class="text-xl font-bold">Chrono</div>
            <div class="text-sm text-gray-400 mt-1">{{ $this->newBoilTime == null ? $this->brewing->boil_time : $this->newBoilTime }} min</div>
        </div>
        {{-- TODO:: if time left > 1 minute, poll every minute, not every second --}}
        @if ($this->brewing->boil_start != null)
            @if (!$this->endTime())
            <span wire:poll.s class="info-label-inprogress-yellow">
                @php
                    $time_left = now()->diffInMinutes(Carbon\Carbon::create($this->brewing->boil_start)->addMinutes($this->brewing->boil_time), false);
                @endphp
                @if ($time_left < 1)
                    @php
                        $seconds_left = now()->diffInSeconds(Carbon\Carbon::create($this->brewing->boil_start)->addMinutes($this->brewing->boil_time));
                    @endphp
                    {{ round($seconds_left) }} {{ Str::plural('seconde', round($seconds_left)) }}
                @else
                    {{ round($time_left) }} {{ Str::plural('minute', round($time_left)) }}
                @endif
                left
            </span>
            @else
            <span class="flex justify-center font-semibold text-tawny pb-2 animate-pulse">Time elapsed</span>
            @endif
        @else
            @if (!$isOpen)
                <div class="flex justify-between w-full gap-4">
                    <button class="btn-yellow w-full" wire:click="startChrono">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="M8 17.175V6.825q0-.425.3-.713t.7-.287q.125 0 .263.037t.262.113l8.15 5.175q.225.15.338.375t.112.475t-.112.475t-.338.375l-8.15 5.175q-.125.075-.262.113T9 18.175q-.4 0-.7-.288t-.3-.712"/></svg>
                        Start
                    </button>
                    <button class="btn-yellow w-full" wire:click="openModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="m12 11.6l2.5 2.5q.275.275.275.7t-.275.7t-.7.275t-.7-.275l-2.8-2.8q-.15-.15-.225-.337T10 11.975V8q0-.425.288-.712T11 7t.713.288T12 8zM18 6h-2q-.425 0-.712-.287T15 5t.288-.712T16 4h2V2q0-.425.288-.712T19 1t.713.288T20 2v2h2q.425 0 .713.288T23 5t-.288.713T22 6h-2v2q0 .425-.288.713T19 9t-.712-.288T18 8zm-7 15q-1.875 0-3.512-.7t-2.863-1.925T2.7 15.512T2 12t.7-3.512t1.925-2.863T7.488 3.7T11 3q.275 0 .513.013t.512.062q.425 0 .713.288t.287.712t-.288.713t-.712.287q-.275 0-.513-.038T11 5Q8.05 5 6.025 7.025T4 12t2.025 4.975T11 19t4.975-2.025T18 12q0-.425.288-.712T19 11t.713.288T20 12q0 1.875-.7 3.513t-1.925 2.862t-2.863 1.925T11 21"/></svg>
                        Change
                    </button>
                </div>
            @endif
        @endif

        @if ($isOpen)
        <div class="flex justify-between w-full gap-4">
            <input class="text-black w-full py-1 text-center focus:outline-none focus:ring-2 focus:ring-old-gold focus:border-old-gold" type="number" wire:model="newBoilTime" placeholder="{{ $brewing->boil_time}}">
            <button class="btn-yellow w-full" wire:click="modifyBoilTime({{ $brewing->id }})">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="M15.275 12.475L11.525 8.7L14.3 5.95l-.725-.725L8.1 10.7L6.7 9.3l5.45-5.475q.6-.6 1.413-.6t1.412.6l.725.725l1.25-1.25q.3-.3.713-.3t.712.3L20.7 5.625q.3.3.3.713t-.3.712zM6.75 21H3v-3.75l7.1-7.125l3.775 3.75z"/></svg>
                Change Time
            </button>
        </div>
        @endif
    </div>

    <div class="gap-y-4 m-4 flex flex-col justify-between items-center">

        <div class="flex items-center text-lg gap-x-2 py-4">
            <svg class="size-8" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor" d="M19 5c-1.11 0-2 .89-2 2v6.76c-.64.57-1 1.39-1 2.24c0 1.66 1.34 3 3 3s3-1.34 3-3c0-.85-.36-1.67-1-2.23V7c0-1.11-.89-2-2-2m0 1c.55 0 1 .45 1 1v1h-2V7c0-.55.45-1 1-1M8 3.54l-.75.84S5.97 5.83 4.68 7.71S2 11.84 2 14c0 3.31 2.69 6 6 6s6-2.69 6-6c0-2.16-1.39-4.41-2.68-6.29S8.75 4.38 8.75 4.38L8 3.54m0 3.13c.44.52.84.95 1.68 2.17C10.89 10.6 12 12.84 12 14c0 2.22-1.78 4-4 4s-4-1.78-4-4c0-1.16 1.11-3.4 2.32-5.16C7.16 7.62 7.56 7.19 8 6.67Z" />
            </svg>
            <span class="border-b-old-gold border-0 border-b-2 w-1/5">Boil</span>
        </div>

        <div class="flex flex-col w-full gap-2">
            @foreach ($steps as $step)
                @php
                    $time_left = now()->diffInMinutes(Carbon\Carbon::create($this->brewing->boil_start)->addMinutes($this->brewing->boil_time)->subMinutes($step->time), false);
                @endphp
                <div class="shadow-md rounded-md border-2 bg-white {{ $step->status ? 'border-old-gold' : ($this->brewing->boil_start != null && $time_left <= 0 ? 'border-red-600' : 'border-transparent') }}"
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
                                    late
                                @elseif ($this->brewing->boil_start != null && $time_left > 0)
                                    in {{ round($time_left) }} {{ Str::plural('minute', round($time_left)) }}
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

        @include('steps._next', ['allChecked' => $this->allChecked && $this->endTime() && ($this->brewing->boil_start != null)])

    </div>
</div>
