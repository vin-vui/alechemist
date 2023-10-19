<div>
    <div class="gap-y-4 m-4 flex flex-col justify-between items-center">
        <div class="flex w-full justify-end">
            <x-note-button />
        </div>
        <div class="flex items-center text-lg gap-x-2 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M21 12s-8.5-2-8.5-10c0 0 8.5 0 8.5 10M3 12C3 2 11.5 2 11.5 2c0 8-8.5 10-8.5 10m9-5.5s1 2.16 3 4c-.24 3.66-3 5.5-3 5.5s-2.76-1.84-3-5.5c2-1.84 3-4 3-4m8.75 6.75S20 17 18 19c0 0-2.47-1.64-3.67-4.19c.72-1.23 1.17-2.69 1.42-3.68c1.38 1.05 3 1.87 5 2.12m-5.25 5c-1 2-3.5 3.5-3.5 3.5s-2.5-1.5-3.5-3.5c0 0 1.09-.91 1.85-2.45c.47.55 1.01.99 1.65 1.2c.64-.21 1.18-.65 1.65-1.2c.76 1.54 1.85 2.45 1.85 2.45m-12.25-5c2-.25 3.62-1.07 5-2.12c.25.99.7 2.45 1.42 3.68C8.47 17.36 6 19 6 19c-2-2-2.75-5.75-2.75-5.75Z" />
            </svg>
            <span class="border-b-old-gold border-0 border-b-2 w-1/5">Mash</span>
        </div>
        <div class="flex flex-col w-full gap-2">
            @foreach ($steps as $step)
                <div class="rounded-md border-2 bg-white {{ $step->status ? 'border-old-gold' : 'border-transparent' }}"
                    wire:click="statusChange({{ $step }})">
                    <div class="rounded-tl-md rounded-tr-md relative flex cursor-pointer p-4 focus:outline-none">
                        <div class="ml-3 flex gap-x-1 justify-between">
                            <div id="privacy-setting-0-label" class="block text-sm font-medium">
                                {{ $step->field }}
                            </div>
                            <div id="privacy-setting-0-description" class="block text-sm">
                                {{ $step->quantity }}
                            </div>
                            <div id="privacy-setting-0-description" class="block text-sm">
                                {{ $step->unit }}
                            </div>
                            <div id="privacy-setting-0-description" class="block text-sm">
                                {{ $step->time }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if ($this->allChecked)
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
