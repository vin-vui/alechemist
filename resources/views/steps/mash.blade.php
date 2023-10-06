<div>
    <div class="flex items-center text-lg gap-x-2 py-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <path fill="currentColor"
                d="M21 12s-8.5-2-8.5-10c0 0 8.5 0 8.5 10M3 12C3 2 11.5 2 11.5 2c0 8-8.5 10-8.5 10m9-5.5s1 2.16 3 4c-.24 3.66-3 5.5-3 5.5s-2.76-1.84-3-5.5c2-1.84 3-4 3-4m8.75 6.75S20 17 18 19c0 0-2.47-1.64-3.67-4.19c.72-1.23 1.17-2.69 1.42-3.68c1.38 1.05 3 1.87 5 2.12m-5.25 5c-1 2-3.5 3.5-3.5 3.5s-2.5-1.5-3.5-3.5c0 0 1.09-.91 1.85-2.45c.47.55 1.01.99 1.65 1.2c.64-.21 1.18-.65 1.65-1.2c.76 1.54 1.85 2.45 1.85 2.45m-12.25-5c2-.25 3.62-1.07 5-2.12c.25.99.7 2.45 1.42 3.68C8.47 17.36 6 19 6 19c-2-2-2.75-5.75-2.75-5.75Z" />
        </svg>
        <span class="border-b-old-gold border-0 border-b-2 w-1/5">Mash</span>
    </div>
    <div class="flex flex-col">
        @foreach ($steps as $step)
                <div class="flex items-center text-sm">
                        <div class="flex items-center">
                            <label for="field"></label>
                            <input class="bg-anti-flash-white border-none w-40 placeholder-black rounded focus:outline-none focus:ring-2"
                                disabled type="text" name="field" placeholder="{{ $step->field }}">
                        </div>
                        <div class="flex flex-col items-center">
                            <label class="font-semibold text-gray-500" for="quantity_brewing">Quantity</label>
                            <input class="bg-gray-50 w-24 h-8 rounded focus:outline-none focus:ring-2" type="number"
                                name="quantity_brewing">
                        </div>
                        <div class="flex flex-col items-center justify-center">
                            <label class="font-semibold text-gray-500" for="time">Minutes</label>
                            <input class="bg-anti-flash-white border-none w-16 placeholder-black rounded focus:outline-none focus:ring-2"
                                disabled type="text" name="time" placeholder="{{ $step->time }}">
                        </div>
                        <div class="flex items-center">
                            <input class="focus:ring-0" type="checkbox" id="check"
                                name="check">
                        </div>
                </div>
        @endforeach
    </div>
    <a href={{ route('boil', $this->brewing) }}>
        <button
            class="bg-xanthous rounded hover:bg-tawny hover:text-white transition-all duration-300 py-1.5 px-3 flex items-center gap-2">
            Next step
        </button>
    </a>
</div>