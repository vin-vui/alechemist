<div>
    <div class="gap-y-4 m-4 flex flex-col justify-between items-center">
        <div class="flex items-center text-lg gap-x-2 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M21 12s-8.5-2-8.5-10c0 0 8.5 0 8.5 10M3 12C3 2 11.5 2 11.5 2c0 8-8.5 10-8.5 10m9-5.5s1 2.16 3 4c-.24 3.66-3 5.5-3 5.5s-2.76-1.84-3-5.5c2-1.84 3-4 3-4m8.75 6.75S20 17 18 19c0 0-2.47-1.64-3.67-4.19c.72-1.23 1.17-2.69 1.42-3.68c1.38 1.05 3 1.87 5 2.12m-5.25 5c-1 2-3.5 3.5-3.5 3.5s-2.5-1.5-3.5-3.5c0 0 1.09-.91 1.85-2.45c.47.55 1.01.99 1.65 1.2c.64-.21 1.18-.65 1.65-1.2c.76 1.54 1.85 2.45 1.85 2.45m-12.25-5c2-.25 3.62-1.07 5-2.12c.25.99.7 2.45 1.42 3.68C8.47 17.36 6 19 6 19c-2-2-2.75-5.75-2.75-5.75Z" />
            </svg>
            <span class="border-b-old-gold border-0 border-b-2 w-1/5">Mash</span>
        </div>
        <div class="flex flex-col w-full">
            @foreach ($steps as $step)
                <fieldset>
                    <div class="rounded-md bg-white">
                        <label
                            class="rounded-tl-md rounded-tr-md relative flex cursor-pointer border p-4 focus:outline-none">
                            <input type="checkbox" name="privacy-setting" value="Public access"
                                class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer text-indigo-600 border-gray-300 focus:ring-indigo-600 active:ring-2 active:ring-offset-2 active:ring-indigo-600">
                            <div class="ml-3 flex gap-x-1 justify-between">
                                <div id="privacy-setting-0-description" class="block text-sm">
                                    {{ $step->field }}
                                </div>
                                <div id="privacy-setting-0-label" class="block text-sm font-medium">
                                    {{ $step->quantity }}
                                </div>
                                <div id="privacy-setting-0-description" class="block text-sm">
                                    {{ $step->unit }}
                                </div>
                                <div id="privacy-setting-0-description" class="block text-sm">
                                    {{ $step->time }}
                                </div>
                            </div>
                        </label>
                    </div>
                </fieldset>
            @endforeach
        </div>
        <a href={{ route('boil', [$recipe, $brewing]) }}>
            <button
                class="bg-xanthous rounded hover:bg-tawny hover:text-white transition-all duration-300 py-1.5 px-3 flex items-center gap-2">
                Next step
            </button>
        </a>
    </div>
</div>
