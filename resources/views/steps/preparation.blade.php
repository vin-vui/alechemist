<div>
    <div class="gap-y-4 m-4 flex flex-col justify-between items-center">
        <div class="flex items-center text-lg gap-x-2 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M7.33 18.33c-.83-1.16-.83-2.5-.83-3.83c1.67 1 3.33 2 4.17 3.17l.33.56v-2.28c-1.5-.9-2.92-1.82-3.67-2.87c-.83-1.16-.83-2.5-.83-3.83c1.67 1 3.33 2 4.17 3.17L11 13v-2.3c-1.5-.9-2.92-1.82-3.67-2.87C6.5 6.67 6.5 5.33 6.5 4c1.67 1 3.33 2 4.17 3.17c.1.14.19.29.27.45c-.17-.62-.28-1.2-.29-1.8c-.01-1.51.65-3.06 1.31-4.61c.69 1.48 1.38 2.97 1.39 4.48c.01.63-.1 1.27-.28 1.9c.08-.14.16-.28.26-.42C14.17 6 15.83 5 17.5 4c0 1.33 0 2.67-.83 3.83C15.92 8.88 14.5 9.8 13 10.7V13l.33-.58c.84-1.17 2.5-2.17 4.17-3.17c0 1.33 0 2.67-.83 3.83c-.75 1.05-2.17 1.97-3.67 2.87v2.28l.33-.56c.84-1.17 2.5-2.17 4.17-3.17c0 1.33 0 2.67-.83 3.83c-.75 1.05-2.17 1.97-3.67 2.87V23h-2v-1.8c-1.5-.9-2.92-1.82-3.67-2.87Z" />
            </svg>
            <span class="border-b-old-gold border-0 border-b-2 w-1/5">Preparation</span>
        </div>
        <div class="flex flex-col w-full gap-2">
            @foreach ($steps as $step)
                <div class="rounded-md border-2 bg-white {{ $step->status ? 'border-old-gold' : 'border-transparent' }}"
                    wire:click="statusChange({{ $step }})">
                    <div class="rounded-tl-md rounded-tr-md relative flex cursor-pointer p-4 focus:outline-none">
                        <div class="ml-3 flex gap-x-1 justify-between">
                            <div id="privacy-setting-0-label" class="block text-sm font-medium">
                                {{ $step->quantity }}
                            </div>
                            <div id="privacy-setting-0-description" class="block text-sm">
                                {{ $step->unit }}
                            </div>
                            <div id="privacy-setting-0-description" class="block text-sm">
                                {{ $step->field }}
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
