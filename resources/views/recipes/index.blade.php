<div class="pb-20">

    {{-- Header --}}
    <div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center shadow-lg z-30">
        <a href="{{ route('alechemist.home') }}">
            <svg class="size-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor" d="M6 19h3v-5q0-.425.288-.712T10 13h4q.425 0 .713.288T15 14v5h3v-9l-6-4.5L6 10zm-2 0v-9q0-.475.213-.9t.587-.7l6-4.5q.525-.4 1.2-.4t1.2.4l6 4.5q.375.275.588.7T20 10v9q0 .825-.588 1.413T18 21h-4q-.425 0-.712-.288T13 20v-5h-2v5q0 .425-.288.713T10 21H6q-.825 0-1.412-.587T4 19m8-6.75"/>
            </svg>
        </a>
        <h2 class="text-xl font-semibold tracking-widest truncate">Recipes</h2>
        <div class="size-6">
            <span class="bg-gray-100 text-gray-600 px-2 rounded">{{ count($this->recipes) }}</span>
        </div>
    </div>

    {{-- New Brewing Fixed Button --}}
    <div class="fixed bottom-4 right-4">
        <a href="{{ route('fileUpload') }}" class="btn-yellow">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="M11 13H5v-2h6V5h2v6h6v2h-6v6h-2v-6Z" />
            </svg>
            <span>Import New Recipe</span>
        </a>
    </div>

    <div class="flex flex-col gap-y-4">
        @foreach ($this->recipes as $recipe)
            <a href="{{ route('recipes.show', $recipe) }}">
                <div class="bg-gray-50 shadow">
                    <div class="flex gap-4 justify-start items-center">
                        <div class="shrink-0">
                            <img class="h-28 object-cover" src="/pictures/recipe.jpg">
                        </div>
                        <div class="w-3/4">
                            <h3 class="truncate mr-4 text-lg font-bold sm:flex text-gray-900 uppercase">{{ $recipe->name }}</h3>
                            <h4 class="text-md text-gray-500 uppercase">{{ $recipe->type }}</h4>
                            @php
                                $brewings = App\Models\Brewing::where('recipe_id', $recipe->id)->count();
                            @endphp
                            @if($brewings > 0)
                            <div class="">
                                <span class="bg-old-gold px-1.5 rounded font-semibold">{{ $brewings }}</span>
                                <span class="text-sm text-gray-400">{{ Str::plural('brewing', $brewings) }} in progress</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
