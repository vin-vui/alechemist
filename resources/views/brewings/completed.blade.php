<div>
    <div class="bg-white sticky px-4 py-2.5 flex justify-between items-center ">
        <h2 class="text-xl px-2 font-semibold tracking-widest">{{ $this->brewing->name }}</h2>
        <a href=" {{ route('brewing.index', [$recipe, $this->brewing]) }}">
            <button
                class="bg-xanthous hover:bg-tawny hover:text-white transition-all duration-300 py-1.5 px-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" fill-rule="evenodd"
                        d="M20 4H4a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1ZM4 2a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h16a3 3 0 0 0 3-3V5a3 3 0 0 0-3-3H4Zm2 5h2v2H6V7Zm5 0a1 1 0 1 0 0 2h6a1 1 0 1 0 0-2h-6Zm-3 4H6v2h2v-2Zm2 1a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2h-6a1 1 0 0 1-1-1Zm-2 3H6v2h2v-2Zm2 1a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2h-6a1 1 0 0 1-1-1Z"
                        clip-rule="evenodd" />
                </svg>
                Brewing list
            </button>
        </a>
    </div>
    <div class="mx-4">
        <div class="flex items-center gap-x-2 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M19 5c-1.11 0-2 .89-2 2v6.76c-.64.57-1 1.39-1 2.24c0 1.66 1.34 3 3 3s3-1.34 3-3c0-.85-.36-1.67-1-2.23V7c0-1.11-.89-2-2-2m0 1c.55 0 1 .45 1 1v1h-2V7c0-.55.45-1 1-1M8 3.54l-.75.84S5.97 5.83 4.68 7.71S2 11.84 2 14c0 3.31 2.69 6 6 6s6-2.69 6-6c0-2.16-1.39-4.41-2.68-6.29S8.75 4.38 8.75 4.38L8 3.54m0 3.13c.44.52.84.95 1.68 2.17C10.89 10.6 12 12.84 12 14c0 2.22-1.78 4-4 4s-4-1.78-4-4c0-1.16 1.11-3.4 2.32-5.16C7.16 7.62 7.56 7.19 8 6.67Z" />
            </svg>
            <span>Boil</span>
        </div>
        <div class="flex justify-end w-full">
            <div class="flex justify-center p-1 text-sm border w-1/3 bg-tawny">
                {{ Carbon\Carbon::parse($brewing->boil_start)->format('j F Y') }}
            </div>
        </div>
        <div class="pb-4">
            <table class="w-full text-sm text-center text-gray-500">
                <thead class="text-xs text-gray-800 uppercase bg-old-gold">
                    <tr>
                        <th scope="col" class="flex justify-center px-2 py-3">
                            Quantity</th>

                        <th scope="col" class="px-2 py-3">Name</th>
                        <th scope="col" class="flex justify-center px-2 py-3">Minutes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->brewing->brewingSteps as $step)
                        <tr class="bg-white border-b">
                            @if ($step->type == 'Boil' || $step->type == 'Aroma')
                                <td class="flex justify-center px-2 py-3">{{ $step->quantity }} {{ $step->unit }}</td>

                                <td class="px-2 py-3">{{ $step->field }} </td>
                                <td class="flex justify-center px-2 py-3">
                                    @if ($step->time != $this->recipeSteps[$loop->index]->time)
                                        {{ $step->time }} <span
                                            class="text-red-500 ml-2">{{ $this->recipeSteps[$loop->index]->time }}</span>
                                    @else
                                        {{ $step->time }}
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex items-center gap-x-2 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M21 13h-6.6l4.7 4.7l-1.4 1.4l-4.7-4.7V21h-2v-6.7L6.3 19l-1.4-1.4L9.4 13H3v-2h6.6L4.9 6.3l1.4-1.4L11 9.6V3h2v6.4l4.6-4.6L19 6.3L14.3 11H21v2Z" />
            </svg>
            <span>Yeasts</span>
        </div>
        <div class="pb-4">
            <table class="w-full text-sm text-center text-gray-500">
                <thead class="text-xs text-gray-800 uppercase bg-old-gold">
                    <tr>
                        <th scope="col" class="flex justify-center px-2 py-3">
                            Quantity</th>
                        <th scope="col" class="px-2 py-3">Name</th>
                        <th scope="col" class="flex justify-center px-2 py-3">
                            Minutes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->brewing->brewingSteps as $step)
                        <tr class="bg-white border-b">
                            @if ($step->type == 'Yeast' || $step->type == 'Dry Hop')
                                <td class="flex justify-center px-2 py-3">
                                    {{ $step->quantity }} {{ $step->unit }}</td>
                                <td class="px-2 py-3">{{ $step->field }} </td>
                                <td class="flex justify-center px-2 py-3">
                                    {{ $step->time }} </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex items-center gap-x-2 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512">
                <path fill="currentColor"
                    d="M200 23v18h21.895l-14.31 123.303c-14.473 8.144-25.962 16.414-34.18 25.265c-9.02 9.712-14.405 20.57-14.405 31.97V445.54c0 11.4 5.042 21.877 12.348 29.794c7.305 7.917 17.208 13.666 28.35 13.666H312c11.23 0 21.24-5.72 28.596-13.645C347.953 467.432 353 456.94 353 445.54v-224c0-11.402-5.386-22.26-14.404-31.972c-8.22-8.85-19.708-17.12-34.18-25.265L290.106 41H312V23H200zm40.016 18h31.968l8.094 69.727c-2.328-.97-4.98-1.573-8.078-1.573c-10.342 0-17.062 6.425-22.15 10.772c-5.09 4.346-5.982 7.135-9.85 6.46c-4.685-.82-6.447-6.444-8.57-11.41L240.016 41zm31.25 86.113c.235.003.48.016.734.04c5.087.508 7.665 5.963 11.2 10.476l1.212 10.438a9.6 10.338 0 0 0-9.213-7.453a9.6 10.338 0 0 0-9.6 10.338a9.6 10.338 0 0 0 9.6 10.338a9.6 10.338 0 0 0 9.585-10.01l2.817 24.265l4.13 2.225c15.45 8.318 26.69 16.527 33.672 24.046c6.982 7.52 9.596 13.893 9.596 19.723v42.69h-25.568A64 94.77 0 0 0 256 221.54a64 94.77 0 0 0-53.416 42.69H177v-42.69c0-5.83 2.614-12.204 9.596-19.724s18.223-15.728 33.672-24.046l4.13-2.225l4.047-34.856c3.09 2.163 6.88 3.695 11.555 3.695c10.237 0 16.543-6.503 21.54-10.772c4.686-4.002 6.196-6.534 9.726-6.5zM256 166.4a9.6 10.338 0 0 0-9.6 10.338a9.6 10.338 0 0 0 9.6 10.338a9.6 10.338 0 0 0 9.6-10.338A9.6 10.338 0 0 0 256 166.4zm19.2 15.57a9.6 10.338 0 0 0-9.6 10.337a9.6 10.338 0 0 0 9.6 10.338a9.6 10.338 0 0 0 9.6-10.338a9.6 10.338 0 0 0-9.6-10.338zM256 247c13.42 0 23.9 9.857 30.578 22.445c1.962 3.698 3.663 7.726 5.098 12.016H336v18h-40.156c.757 5.4 1.156 11.038 1.156 16.85c0 12.354-1.775 23.944-5.06 34.075H336v18h-52.535c-6.64 9.987-15.97 17.23-27.465 17.23c-11.496 0-20.825-7.243-27.465-17.23H176v-18h44.06c-3.285-10.13-5.06-21.72-5.06-34.076c0-5.812.4-11.45 1.156-16.85H176v-18h44.324c1.435-4.29 3.136-8.317 5.098-12.015C232.102 256.857 242.58 247 256 247zm0 18c-4.253 0-9.775 3.644-14.678 12.883c-4.902 9.24-8.322 23.063-8.322 38.426c0 15.362 3.42 29.183 8.322 38.422c4.903 9.24 10.425 12.883 14.678 12.883s9.775-3.643 14.678-12.883c4.902-9.24 8.322-23.06 8.322-38.423c0-15.364-3.42-29.188-8.322-38.427C265.775 268.643 260.253 265 256 265zm-79 120.615h35.47a64 94.77 0 0 0 43.53 25.46a64 94.77 0 0 0 43.572-25.46H335v59.924c0 5.83-2.953 12.567-7.596 17.567c-4.643 5-10.635 7.893-15.404 7.893H199.697c-4.555 0-10.502-2.867-15.12-7.873c-4.62-5.006-7.577-11.758-7.577-17.588v-59.925z" />
            </svg>
            <div class="flex w-full justify-between items-center mr-2">
                <div>Ferment</div>
            </div>
        </div>
        <div class="flex w-full">
            <div class="flex justify-center p-1 text-sm border w-1/2 bg-tawny">
                {{ Carbon\Carbon::parse($brewing->ferment_start)->format('j F Y') }}
            </div>
            <div class="flex justify-center p-1 text-sm border w-1/2 bg-tawny">
                {{ Carbon\Carbon::parse($brewing->ferment_end)->format('j F Y') }}
            </div>
        </div>
        <div class="pb-4">
            <table class="w-full text-sm text-center text-gray-500">
                <thead class="text-xs text-gray-800 uppercase bg-old-gold">
                    <tr>
                        <th scope="col" class="px-4 py-3">Step</th>
                        <th scope="col" class="px-4 py-3">Days</th>
                        <th scope="col" class="px-4 py-3">Temperature</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->brewing->brewingSteps as $step)
                        <tr class="bg-white border-b">
                            @if ($step->type == 'Primary' || $step->type == 'Secondary' || $step->type == 'Tertiary' || $step->type == 'Bottle')
                                <td class="px-4 py-3">{{ $step->field }} </td>
                                <td class="px-4 py-3">{{ $step->time / 1440 }} </td>
                                <td class="px-4 py-3">{{ $step->quantity }} {{ $step->unit }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="py-4">
            <x-note-button />
        </div>
        <div class="pb-4">
            <div class="text-sm text-gray-500 bg-white p-2">
                <textarea rows="10" class="flex w-full border-none">{{ $this->brewing->note }}</textarea>
            </div>
        </div>
        <div>
            <button wire:click="delete({{ $brewing }})"
                class="bg-tawny rounded mx-auto hover:bg-licorice hover:text-white transition-all duration-300 py-1.5 px-3 flex w-full items-center justify-center my-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30"
                    viewBox="0 0 512 512">
                    <path fill="none" stroke="currentColor" stroke-linecap="round"
                        stroke-linejoin="round" stroke-width="32"
                        d="m112 112l20 320c.95 18.49 14.4 32 32 32h184c17.67 0 30.87-13.51 32-32l20-320" />
                    <path fill="currentColor" stroke="currentColor" stroke-linecap="round"
                        stroke-miterlimit="10" stroke-width="32" d="M80 112h352" />
                    <path fill="none" stroke="currentColor" stroke-linecap="round"
                        stroke-linejoin="round" stroke-width="32"
                        d="M192 112V72h0a23.93 23.93 0 0 1 24-24h80a23.93 23.93 0 0 1 24 24h0v40m-64 64v224m-72-224l8 224m136-224l-8 224" />
                </svg>
            </button>
        </div>
    </div>
</div>
