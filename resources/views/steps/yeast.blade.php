<div>
    <div class="gap-y-4 m-4 flex flex-col justify-between items-center">
        <div class="flex w-full justify-end">
            <x-note-button />
        </div>
        <div class="flex items-center text-lg gap-x-2 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M21 13h-6.6l4.7 4.7l-1.4 1.4l-4.7-4.7V21h-2v-6.7L6.3 19l-1.4-1.4L9.4 13H3v-2h6.6L4.9 6.3l1.4-1.4L11 9.6V3h2v6.4l4.6-4.6L19 6.3L14.3 11H21v2Z" />
            </svg>
            <span class="border-b-old-gold border-0 border-b-2 w-1/5">Yeasts</span>
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
