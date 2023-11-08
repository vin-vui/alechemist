<div>
    <div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center">
        <h2 class="text-xl font-semibold tracking-widest">Brewing</h2>
        <a href="{{ route('brewing.create', $recipe) }}">
            <button
                class="bg-xanthous rounded hover:bg-tawny hover:text-white transition-all duration-300 py-1.5 px-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M11 13H5v-2h6V5h2v6h6v2h-6v6h-2v-6Z" />
                </svg>
                New Brewing
            </button>
        </a>
    </div>
    <div class="flex flex-col gap-y-4 p-4">
        @foreach ($brewings as $brewing)
            <div class="flex bg-gray-50 shadow-lg">
                @if ($brewing->current_step != null)
                    <a href="{{ route($brewing->current_step, [$recipe, $brewing]) }}" class="flex flex-col w-full">
                    @else
                        <a href="{{ route('preparation', [$recipe, $brewing]) }}" class="flex flex-col w-full">
                @endif
                <div class="flex justify-start items-center">
                    <div class="flex shrink-0 w-1/4">
                        <img class="h-32 object-cover" src="/pictures/brew.jpg">
                    </div>
                    <div class="flex flex-col gap-y-2 w-full">
                        <div class="flex px-2">
                            <h3 class="text-lg font-semibold sm:flex text-gray-900 uppercase">
                                {{ $brewing->name }}
                            </h3>
                        </div>
                        <div
                            class="flex justify-start text-gray-500 capitalize mx-4 {{ $brewing->current_step ? '' : 'hidden' }}">
                            {{ $brewing->current_step }}
                        </div>
                        <div class="flex justify-between gap-x-2 items-center pl-2">
                            <div class="text-xs bg-old-gold py-0.5 px-1.5 rounded text-gray-900 uppercase">
                                {{ $brewing->alcohol }} %
                            </div>
                            <div class="text-xs min-w-fit py-1 px-1.5 rounded text-gray-900 uppercase">
                                {{ Carbon\Carbon::parse($brewing->date_start)->format('j m Y') }}
                            </div>
                            <div class="self-end p-2">
                                <button wire:click="delete({{ $brewing }})"
                                    class="bg-tawny rounded hover:bg-licorice hover:text-white transition-all duration-300 py-1.5 px-3 flex items-center my-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
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
                </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
