<div>

    @include('steps._header', ['recipe' => $this->recipe, 'brewing' => $this->brewing])

    <div class="gap-y-4 m-4 flex flex-col justify-between items-center">
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

        @include('steps._next', ['allChecked' => $this->allChecked])

    </div>
</div>
