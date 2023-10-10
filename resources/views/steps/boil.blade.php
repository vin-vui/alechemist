<div>
    <div class="gap-y-4 m-4 flex flex-col justify-between items-center">
        <div class="flex items-center text-lg gap-x-2 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M19 5c-1.11 0-2 .89-2 2v6.76c-.64.57-1 1.39-1 2.24c0 1.66 1.34 3 3 3s3-1.34 3-3c0-.85-.36-1.67-1-2.23V7c0-1.11-.89-2-2-2m0 1c.55 0 1 .45 1 1v1h-2V7c0-.55.45-1 1-1M8 3.54l-.75.84S5.97 5.83 4.68 7.71S2 11.84 2 14c0 3.31 2.69 6 6 6s6-2.69 6-6c0-2.16-1.39-4.41-2.68-6.29S8.75 4.38 8.75 4.38L8 3.54m0 3.13c.44.52.84.95 1.68 2.17C10.89 10.6 12 12.84 12 14c0 2.22-1.78 4-4 4s-4-1.78-4-4c0-1.16 1.11-3.4 2.32-5.16C7.16 7.62 7.56 7.19 8 6.67Z" />
            </svg>
            <span class="border-b-old-gold border-0 border-b-2 w-1/5">Boil</span>
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
                                    {{ $step->quantity }}
                                </div>
                                <div id="privacy-setting-0-label" class="block text-sm font-medium">
                                    {{ $step->unit }}
                                </div>
                                <div id="privacy-setting-0-description" class="block text-sm">
                                    {{ $step->field }}
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
        <a href={{ route('yeast', [$recipe, $brewing]) }}>
            <button
                class="bg-xanthous rounded hover:bg-tawny hover:text-white transition-all duration-300 py-1.5 px-3 flex items-center gap-2">
                Next step
            </button>
        </a>
    </div>
</div>
