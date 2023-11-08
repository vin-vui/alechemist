<div>
    <div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center">
        <h2 class="text-xl font-semibold tracking-widest">Recipes</h2>
        <a href="{{ route('fileUpload') }}">
            <button
                class="bg-xanthous hover:bg-tawny hover:text-white transition-all duration-300 py-1.5 px-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M11 13H5v-2h6V5h2v6h6v2h-6v6h-2v-6Z" />
                </svg>
                Import New Recipe
            </button>
        </a>
    </div>
    <div class="flex flex-col gap-y-4 px-4 sm:px-6 lg:px-8 pt-8 pb-12">
        @foreach ($recipes as $recipe)
            <a href="{{ route('recipes.show', $recipe) }}">
                <div class="bg-gray-50 shadow-lg dark:bg-gray-700 dark:text-gray-400">
                    <div class="flex gap-4 justify-start items-center">
                        <div class="shrink-0">
                            <img class="h-28 object-cover" src="/pictures/recipe.jpg">
                        </div>
                        <div class="w-3/4">
                            <h3 class="truncate mr-4 text-lg font-bold sm:flex text-gray-900 uppercase">
                                {{ $recipe->name }}
                            </h3>
                            <h4 class="text-md text-gray-500 uppercase">{{ $recipe->type }}</h4>
                            <span
                                class="text-xs bg-xanthous py-0.5 px-1.5 rounded text-gray-900 uppercase">{{ $recipe->alcohol }}
                                %
                            </span>
                            <div class="flex justify-between items-center">
                                <div class="flex flex-col justify-between pt-1">
                                    <div class="text-xs">Created {{ $recipe->created_at->format('d-m-Y') }}</div>
                                    <div class="text-xs">Modified {{ $recipe->updated_at->format('d-m-Y') }}</div>
                                </div>
                            </a>
                                <div class="px-2">
                                <button wire:click="delete({{ $recipe }})"
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
                </div>

        @endforeach
    </div>
</div>
