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
                            <img class="h-28 object-cover"
                                src="/pictures/recipe.jpg">
                        </div>
                        <div class="w-3/4">
                            <h3 class="truncate mr-4 text-lg font-bold sm:flex text-gray-900 uppercase">
                                {{ $recipe->name }}</h3>
                            <h4 class="text-md text-gray-500 uppercase">{{ $recipe->type }}</h4>
                            <span
                                class="text-xs bg-xanthous py-0.5 px-1.5 rounded text-gray-900 uppercase">{{ $recipe->alcohol }}
                                %</span>
                            <div class="flex justify-between pt-1 pr-4">
                                <div class="text-xs">Created {{ $recipe->created_at->format('d-m-Y') }}</div>
                                <div class="text-xs">Modified {{ $recipe->updated_at->format('d-m-Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
