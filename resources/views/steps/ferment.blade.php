<div class="pb-20">

    @include('steps._header', ['recipe' => $this->recipe, 'brewing' => $this->brewing])

    <div class="gap-y-4 m-4 flex flex-col justify-between items-center">

        <div class="flex items-center text-lg gap-x-2 py-4">
            <svg class="size-8" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512">
                <path fill="currentColor" d="M200 23v18h21.895l-14.31 123.303c-14.473 8.144-25.962 16.414-34.18 25.265c-9.02 9.712-14.405 20.57-14.405 31.97V445.54c0 11.4 5.042 21.877 12.348 29.794c7.305 7.917 17.208 13.666 28.35 13.666H312c11.23 0 21.24-5.72 28.596-13.645C347.953 467.432 353 456.94 353 445.54v-224c0-11.402-5.386-22.26-14.404-31.972c-8.22-8.85-19.708-17.12-34.18-25.265L290.106 41H312V23H200zm40.016 18h31.968l8.094 69.727c-2.328-.97-4.98-1.573-8.078-1.573c-10.342 0-17.062 6.425-22.15 10.772c-5.09 4.346-5.982 7.135-9.85 6.46c-4.685-.82-6.447-6.444-8.57-11.41L240.016 41zm31.25 86.113c.235.003.48.016.734.04c5.087.508 7.665 5.963 11.2 10.476l1.212 10.438a9.6 10.338 0 0 0-9.213-7.453a9.6 10.338 0 0 0-9.6 10.338a9.6 10.338 0 0 0 9.6 10.338a9.6 10.338 0 0 0 9.585-10.01l2.817 24.265l4.13 2.225c15.45 8.318 26.69 16.527 33.672 24.046c6.982 7.52 9.596 13.893 9.596 19.723v42.69h-25.568A64 94.77 0 0 0 256 221.54a64 94.77 0 0 0-53.416 42.69H177v-42.69c0-5.83 2.614-12.204 9.596-19.724s18.223-15.728 33.672-24.046l4.13-2.225l4.047-34.856c3.09 2.163 6.88 3.695 11.555 3.695c10.237 0 16.543-6.503 21.54-10.772c4.686-4.002 6.196-6.534 9.726-6.5zM256 166.4a9.6 10.338 0 0 0-9.6 10.338a9.6 10.338 0 0 0 9.6 10.338a9.6 10.338 0 0 0 9.6-10.338A9.6 10.338 0 0 0 256 166.4zm19.2 15.57a9.6 10.338 0 0 0-9.6 10.337a9.6 10.338 0 0 0 9.6 10.338a9.6 10.338 0 0 0 9.6-10.338a9.6 10.338 0 0 0-9.6-10.338zM256 247c13.42 0 23.9 9.857 30.578 22.445c1.962 3.698 3.663 7.726 5.098 12.016H336v18h-40.156c.757 5.4 1.156 11.038 1.156 16.85c0 12.354-1.775 23.944-5.06 34.075H336v18h-52.535c-6.64 9.987-15.97 17.23-27.465 17.23c-11.496 0-20.825-7.243-27.465-17.23H176v-18h44.06c-3.285-10.13-5.06-21.72-5.06-34.076c0-5.812.4-11.45 1.156-16.85H176v-18h44.324c1.435-4.29 3.136-8.317 5.098-12.015C232.102 256.857 242.58 247 256 247zm0 18c-4.253 0-9.775 3.644-14.678 12.883c-4.902 9.24-8.322 23.063-8.322 38.426c0 15.362 3.42 29.183 8.322 38.422c4.903 9.24 10.425 12.883 14.678 12.883s9.775-3.643 14.678-12.883c4.902-9.24 8.322-23.06 8.322-38.423c0-15.364-3.42-29.188-8.322-38.427C265.775 268.643 260.253 265 256 265zm-79 120.615h35.47a64 94.77 0 0 0 43.53 25.46a64 94.77 0 0 0 43.572-25.46H335v59.924c0 5.83-2.953 12.567-7.596 17.567c-4.643 5-10.635 7.893-15.404 7.893H199.697c-4.555 0-10.502-2.867-15.12-7.873c-4.62-5.006-7.577-11.758-7.577-17.588v-59.925z" />
            </svg>
            <span class="border-b-old-gold border-0 border-b-2 w-1/5">Ferment</span>
        </div>

        <div class="flex flex-col w-full gap-2">
            @php
            $totalTime = 0;
            @endphp
            @foreach ($this->steps as $step)
            @php
            $totalTime = $totalTime + $step->time;
            $time_left = now()->diffInDays(Carbon\Carbon::create($this->brewing->ferment_start)->addMinutes($totalTime), false);
            @endphp
            <div class="rounded-md border-2 bg-white {{ $step->status ? 'border-old-gold' : ($this->brewing->ferment_start != null && $time_left <= 0 ? 'border-red-600' : 'border-transparent') }}" wire:click="statusChange({{ $step }})">
                <div class="rounded-tl-md rounded-tr-md relative flex cursor-pointer p-4 focus:outline-none">
                    <div class="w-full flex flex-col">
                        <div class="flex items-center w-full gap-x-4 justify-between">
                            <div class="info-label text-sm">
                                {{ round(- Carbon\Carbon::now()->addMinutes($step->time)->diffInDays()) }} {{ __('days') }}
                            </div>
                            <div class="block text-sm truncate">
                                {{ $step->field }}
                            </div>
                            <div class="block text-sm whitespace-nowrap">
                                {{ $step->quantity }} {{ $step->unit }}
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex justify-end text-sm">
                                {{ __('ends') }} {{ Carbon\Carbon::create($this->brewing->ferment_start)->addMinutes($totalTime)->format('d/m/Y') }}
                            </div>
                            <div class="info-label-yellow text-sm">
                                {{ $time_left <= 0 ? 'finished' : round($time_left) . ' ' . __('days left') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @include('steps._next', ['allChecked' => $this->allChecked && ($this->brewing->ferment_start != null)])

    </div>
</div>
