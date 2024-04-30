<div class="pb-4">

    {{-- Header --}}
    <div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center shadow-lg z-30">
        <button wire:click="back">
            <svg class="size-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="m10.8 12l3.9 3.9q.275.275.275.7t-.275.7t-.7.275t-.7-.275l-4.6-4.6q-.15-.15-.212-.325T8.425 12t.063-.375t.212-.325l4.6-4.6q.275-.275.7-.275t.7.275t.275.7t-.275.7z"/>
            </svg>
        </button>
        <h2 class="text-xl font-semibold tracking-widest truncate">{{ $recipe->name }}</h2>
        {{-- TODO faire la vue pour modifier la recette --}}
        <button>
            <svg class="size-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="M9 15v-4.25l9.175-9.175q.3-.3.675-.45t.75-.15q.4 0 .763.15t.662.45L22.425 3q.275.3.425.663T23 4.4t-.137.738t-.438.662L13.25 15zm10.6-9.2l1.425-1.4l-1.4-1.4L18.2 4.4zM5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h8.925L7 9.925V17h7.05L21 10.05V19q0 .825-.587 1.413T19 21z"/>
            </svg>
        </button>
    </div>

    {{-- New Brewing Fixed Button --}}
    <div class="fixed bottom-4 right-4">
        <a href="{{ route('brewing.create', $recipe) }}" class="btn-yellow">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="M11 13H5v-2h6V5h2v6h6v2h-6v6h-2v-6Z" />
            </svg>
            <span>New Brewing</span>
        </a>
    </div>

    {{-- Informations --}}
    <div class="bg-white shadow-lg relative z-20">
        <div class="flex gap-4 items-center">
            <div class="shrink-0">
                <img class="h-32 object-cover" src="/pictures/recipe.jpg">
            </div>
            <div class="flex flex-col">
                <h4 class="text-md text-gray-500 uppercase">{{ $recipe->type }}</h4>
                <div class="">
                    <span class="text-xs bg-old-gold py-0.5 px-1.5 rounded text-gray-900 uppercase">{{ $recipe->alcohol }} %</span>
                </div>
                <div class="">
                    <span class="text-xs bg-gray-100 flex-none text-gray-500 py-0.5 px-1.5 rounded">Created at {{ $recipe->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="">
                    <span class="text-xs bg-gray-100 flex-none text-gray-500 py-0.5 px-1.5 rounded">Updatet at {{ $recipe->updated_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- More Informations --}}
    <div class="flex w-full items-center bg-gray-50 mb-8 pt-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="84" height="84" viewBox="0 0 24 24" class="ebc-{{ $recipe->color }}">
            <path fill="currentColor" d="M4 2h15l-2 20H6L4 2m2.2 2l1.6 16h1L7.43 6.34C8.5 6 9.89 5.89 11 7c1.56 1.56 4.33.69 5.5.23L16.8 4H6.2Z" />
        </svg>
        <div class="grid grid-cols-2 text-sm gap-4">
            <div class="grid grid-cols-2 gap-x-2 gap-y-1">
                <div class="font-semibold">Method</div>
                <div class="text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ $recipe->method }}</div>
                <div class="font-semibold">Volume</div>
                <div class="text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ $recipe->volume }} l</div>
                <div class="font-semibold">Ferment</div>
                <div class="text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ $recipe->ferment }}</div>
            </div>
            <div class="grid grid-cols-2 gap-x-2 gap-y-1">
                <div class="font-semibold truncate">Initial Density</div>
                <div class="text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ $recipe->initial_density }}</div>
                <div class="font-semibold truncate">Final Density</div>
                <div class="text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ $recipe->final_density }}</div>
                <div class="font-semibold truncate">Carbonation</div>
                <div class="text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ $recipe->carbonation }}</div>
            </div>
        </div>
    </div>

    {{-- Brewing List Button --}}
    @if($this->inProgressCount > 0)
    <div class="sticky top-24 px-4 pb-4">
        <a href="{{ route('brewing.index', $recipe) }}" class="btn-yellow">
            <div class="bg-gray-100 px-1.5 rounded font-semibold">{{ $this->inProgressCount }}</div>
            <div>{{ Str::plural('Bewing', $this->inProgressCount) }} in progress</div>
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor" d="M12.6 12L8.7 8.1q-.275-.275-.275-.7t.275-.7t.7-.275t.7.275l4.6 4.6q.15.15.213.325t.062.375t-.062.375t-.213.325l-4.6 4.6q-.275.275-.7.275t-.7-.275t-.275-.7t.275-.7z"/>
            </svg>
        </a>
    </div>
    @endif

    {{-- Recipe --}}
    <div class="">
        {{-- Preparation --}}
        <div>
            <div class="flex items-center gap-x-2 p-2">
                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                    <path fill="currentColor"d="M7.33 18.33c-.83-1.16-.83-2.5-.83-3.83c1.67 1 3.33 2 4.17 3.17l.33.56v-2.28c-1.5-.9-2.92-1.82-3.67-2.87c-.83-1.16-.83-2.5-.83-3.83c1.67 1 3.33 2 4.17 3.17L11 13v-2.3c-1.5-.9-2.92-1.82-3.67-2.87C6.5 6.67 6.5 5.33 6.5 4c1.67 1 3.33 2 4.17 3.17c.1.14.19.29.27.45c-.17-.62-.28-1.2-.29-1.8c-.01-1.51.65-3.06 1.31-4.61c.69 1.48 1.38 2.97 1.39 4.48c.01.63-.1 1.27-.28 1.9c.08-.14.16-.28.26-.42C14.17 6 15.83 5 17.5 4c0 1.33 0 2.67-.83 3.83C15.92 8.88 14.5 9.8 13 10.7V13l.33-.58c.84-1.17 2.5-2.17 4.17-3.17c0 1.33 0 2.67-.83 3.83c-.75 1.05-2.17 1.97-3.67 2.87v2.28l.33-.56c.84-1.17 2.5-2.17 4.17-3.17c0 1.33 0 2.67-.83 3.83c-.75 1.05-2.17 1.97-3.67 2.87V23h-2v-1.8c-1.5-.9-2.92-1.82-3.67-2.87Z" />
                </svg>
                <span>Preparation</span>
            </div>
            <div class="pb-4">
                <table class="w-full text-sm text-gray-500">
                    <thead class="text-xs text-gray-800 uppercase bg-old-gold">
                        <tr>
                            <th scope="col" class="text-left px-4 py-3">Name</th>
                            <th scope="col" class="text-right px-4 py-3">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recipe->steps->where('type', 'Preparation') as $step)
                            <tr class="text-center bg-white border-b">
                                <td class="text-left px-4 py-3">{{ $step->field }} </td>
                                <td class="text-right px-4 py-3">{{ $step->quantity }} {{ $step->unit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mash --}}
        <div>
            <div class="flex items-center gap-x-2 p-2">
                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M21 12s-8.5-2-8.5-10c0 0 8.5 0 8.5 10M3 12C3 2 11.5 2 11.5 2c0 8-8.5 10-8.5 10m9-5.5s1 2.16 3 4c-.24 3.66-3 5.5-3 5.5s-2.76-1.84-3-5.5c2-1.84 3-4 3-4m8.75 6.75S20 17 18 19c0 0-2.47-1.64-3.67-4.19c.72-1.23 1.17-2.69 1.42-3.68c1.38 1.05 3 1.87 5 2.12m-5.25 5c-1 2-3.5 3.5-3.5 3.5s-2.5-1.5-3.5-3.5c0 0 1.09-.91 1.85-2.45c.47.55 1.01.99 1.65 1.2c.64-.21 1.18-.65 1.65-1.2c.76 1.54 1.85 2.45 1.85 2.45m-12.25-5c2-.25 3.62-1.07 5-2.12c.25.99.7 2.45 1.42 3.68C8.47 17.36 6 19 6 19c-2-2-2.75-5.75-2.75-5.75Z" />
                </svg>
                <span>Mash</span>
            </div>
            <div class="pb-4">
                <table class="w-full text-sm text-gray-500">
                    <thead class="text-xs text-gray-800 uppercase bg-old-gold">
                        <tr>
                            <th scope="col" class="text-left px-2 py-3">Name</th>
                            <th scope="col" class="px-2 py-3">Temp.</th>
                            <th scope="col" class="text-right px-2 py-3">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recipe->steps->where('type', 'Mash') as $step)
                            <tr class="text-center bg-white border-b">
                                <td class="text-left px-2 py-3">{{ $step->field }}</td>
                                <td class="px-2 py-3">{{ $step->quantity }} {{ $step->unit }}</td>
                                <td class="text-right px-2 py-3">{{ $step->time ? $step->time.' min' : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Boil --}}
        <div>
            <div class="flex items-center gap-x-2 p-2">
                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M19 5c-1.11 0-2 .89-2 2v6.76c-.64.57-1 1.39-1 2.24c0 1.66 1.34 3 3 3s3-1.34 3-3c0-.85-.36-1.67-1-2.23V7c0-1.11-.89-2-2-2m0 1c.55 0 1 .45 1 1v1h-2V7c0-.55.45-1 1-1M8 3.54l-.75.84S5.97 5.83 4.68 7.71S2 11.84 2 14c0 3.31 2.69 6 6 6s6-2.69 6-6c0-2.16-1.39-4.41-2.68-6.29S8.75 4.38 8.75 4.38L8 3.54m0 3.13c.44.52.84.95 1.68 2.17C10.89 10.6 12 12.84 12 14c0 2.22-1.78 4-4 4s-4-1.78-4-4c0-1.16 1.11-3.4 2.32-5.16C7.16 7.62 7.56 7.19 8 6.67Z" />
                </svg>
                <span>Boil</span>
            </div>
            <div class="pb-4">
                <table class="w-full text-sm text-gray-500">
                    <thead class="text-xs text-gray-800 uppercase bg-old-gold">
                        <tr>
                            <th scope="col" class="text-left px-2 py-3">Add</th>
                            <th scope="col" class="text-left px-2 py-3">Qty.</th>
                            <th scope="col" class="text-right px-2 py-3">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recipe->steps->whereIn('type', ['Boil', 'Aroma']) as $step)
                            <tr class="text-center bg-white border-b">
                                <td class="text-left px-2 py-3">{{ $step->field }} <span class="text-gray-300 text-xs">({{ $step->type }})</span></td>
                                <td class="text-left px-2 py-3">{{ $step->quantity }} {{ $step->unit }}</td>
                                <td class="text-right px-2 py-3">{{ $step->time ? $step->time.' min' : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Yeasts & Dry Hop --}}
        <div>
            <div class="flex items-center gap-x-2 p-2">
                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M21 13h-6.6l4.7 4.7l-1.4 1.4l-4.7-4.7V21h-2v-6.7L6.3 19l-1.4-1.4L9.4 13H3v-2h6.6L4.9 6.3l1.4-1.4L11 9.6V3h2v6.4l4.6-4.6L19 6.3L14.3 11H21v2Z" />
                </svg>
                <span>Yeasts & Dry Hop</span>
            </div>
            <div class="pb-4">
                <table class="w-full text-sm text-gray-500">
                    <thead class="text-xs text-gray-800 uppercase bg-old-gold">
                        <tr>
                            <th scope="col" class="text-left px-2 py-3">Add</th>
                            <th scope="col" class="text-left px-2 py-3">Qty.</th>
                            <th scope="col" class="text-right px-2 py-3">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recipe->steps->where('type', 'Yeast') as $step)
                            <tr class="text-center bg-white border-b">
                                <td class="text-left px-2 py-3">{{ $step->field }} <span class="text-gray-300 text-xs">({{ $step->type }})</span></td>
                                <td class="text-left px-2 py-3">{{ $step->quantity }} {{ $step->unit }}</td>
                                <td class="text-right px-2 py-3"></td>
                            </tr>
                        @endforeach
                        @foreach ($recipe->steps->where('type', 'Dry Hop') as $step)
                            <tr class="text-center bg-white border-b">
                                <td class="text-left px-2 py-3">{{ $step->field }} <span class="text-gray-300 text-xs">({{ $step->type }})</span></td>
                                <td class="text-left px-2 py-3">{{ $step->quantity }} {{ $step->unit }}</td>
                                <td class="text-right px-2 py-3">{{ $step->time / 1440 }} days</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Ferment --}}
        <div>
            <div class="flex items-center gap-x-2 p-2">
                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512">
                    <path fill="currentColor" d="M200 23v18h21.895l-14.31 123.303c-14.473 8.144-25.962 16.414-34.18 25.265c-9.02 9.712-14.405 20.57-14.405 31.97V445.54c0 11.4 5.042 21.877 12.348 29.794c7.305 7.917 17.208 13.666 28.35 13.666H312c11.23 0 21.24-5.72 28.596-13.645C347.953 467.432 353 456.94 353 445.54v-224c0-11.402-5.386-22.26-14.404-31.972c-8.22-8.85-19.708-17.12-34.18-25.265L290.106 41H312V23H200zm40.016 18h31.968l8.094 69.727c-2.328-.97-4.98-1.573-8.078-1.573c-10.342 0-17.062 6.425-22.15 10.772c-5.09 4.346-5.982 7.135-9.85 6.46c-4.685-.82-6.447-6.444-8.57-11.41L240.016 41zm31.25 86.113c.235.003.48.016.734.04c5.087.508 7.665 5.963 11.2 10.476l1.212 10.438a9.6 10.338 0 0 0-9.213-7.453a9.6 10.338 0 0 0-9.6 10.338a9.6 10.338 0 0 0 9.6 10.338a9.6 10.338 0 0 0 9.585-10.01l2.817 24.265l4.13 2.225c15.45 8.318 26.69 16.527 33.672 24.046c6.982 7.52 9.596 13.893 9.596 19.723v42.69h-25.568A64 94.77 0 0 0 256 221.54a64 94.77 0 0 0-53.416 42.69H177v-42.69c0-5.83 2.614-12.204 9.596-19.724s18.223-15.728 33.672-24.046l4.13-2.225l4.047-34.856c3.09 2.163 6.88 3.695 11.555 3.695c10.237 0 16.543-6.503 21.54-10.772c4.686-4.002 6.196-6.534 9.726-6.5zM256 166.4a9.6 10.338 0 0 0-9.6 10.338a9.6 10.338 0 0 0 9.6 10.338a9.6 10.338 0 0 0 9.6-10.338A9.6 10.338 0 0 0 256 166.4zm19.2 15.57a9.6 10.338 0 0 0-9.6 10.337a9.6 10.338 0 0 0 9.6 10.338a9.6 10.338 0 0 0 9.6-10.338a9.6 10.338 0 0 0-9.6-10.338zM256 247c13.42 0 23.9 9.857 30.578 22.445c1.962 3.698 3.663 7.726 5.098 12.016H336v18h-40.156c.757 5.4 1.156 11.038 1.156 16.85c0 12.354-1.775 23.944-5.06 34.075H336v18h-52.535c-6.64 9.987-15.97 17.23-27.465 17.23c-11.496 0-20.825-7.243-27.465-17.23H176v-18h44.06c-3.285-10.13-5.06-21.72-5.06-34.076c0-5.812.4-11.45 1.156-16.85H176v-18h44.324c1.435-4.29 3.136-8.317 5.098-12.015C232.102 256.857 242.58 247 256 247zm0 18c-4.253 0-9.775 3.644-14.678 12.883c-4.902 9.24-8.322 23.063-8.322 38.426c0 15.362 3.42 29.183 8.322 38.422c4.903 9.24 10.425 12.883 14.678 12.883s9.775-3.643 14.678-12.883c4.902-9.24 8.322-23.06 8.322-38.423c0-15.364-3.42-29.188-8.322-38.427C265.775 268.643 260.253 265 256 265zm-79 120.615h35.47a64 94.77 0 0 0 43.53 25.46a64 94.77 0 0 0 43.572-25.46H335v59.924c0 5.83-2.953 12.567-7.596 17.567c-4.643 5-10.635 7.893-15.404 7.893H199.697c-4.555 0-10.502-2.867-15.12-7.873c-4.62-5.006-7.577-11.758-7.577-17.588v-59.925z" />
                </svg>
                <div class="flex w-full justify-between items-center mr-2">
                    Ferment
                </div>
            </div>
            <div class="pb-4">
                <table class="w-full text-sm text-gray-500">
                    <thead class="text-xs text-gray-800 uppercase bg-old-gold">
                        <tr>
                            <th scope="col" class="text-left px-2 py-3">Step</th>
                            <th scope="col" class="text-left px-2 py-3">Temp.</th>
                            <th scope="col" class="text-right px-2 py-3">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recipe->steps->whereIn('type', ['Primary', 'Secondary', 'Tertiary', 'Bottle']) as $step)
                            <tr class="text-center bg-white border-b">
                                <td class="text-left px-2 py-3">{{ $step->field }} </td>
                                <td class="text-left px-2 py-3">{{ $step->quantity }} {{ $step->unit }}</td>
                                <td class="text-right px-2 py-3">{{ $step->time / 1440 }} days</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Delete Button --}}
    <div class="flex pt-4 px-4">
        <button wire:click="delete({{ $recipe }})"
            class="bg-transparent border-2 border-tawny transition-all duration-300 py-1.5 px-3 flex items-center gap-2 justify-center">
            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 512 512">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="m112 112l20 320c.95 18.49 14.4 32 32 32h184c17.67 0 30.87-13.51 32-32l20-320" />
                <path fill="currentColor" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M80 112h352" />
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M192 112V72h0a23.93 23.93 0 0 1 24-24h80a23.93 23.93 0 0 1 24 24h0v40m-64 64v224m-72-224l8 224m136-224l-8 224" />
            </svg>
            <span>Delete recipe</span>
        </button>
    </div>

</div>
