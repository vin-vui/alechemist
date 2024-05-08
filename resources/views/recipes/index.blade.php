<div class="pb-20">

    {{-- Header --}}
    <div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center shadow-lg z-30">
        <a href="{{ route('alechemist.home') }}">
            <svg class="size-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor" d="M6 19h3v-5q0-.425.288-.712T10 13h4q.425 0 .713.288T15 14v5h3v-9l-6-4.5L6 10zm-2 0v-9q0-.475.213-.9t.587-.7l6-4.5q.525-.4 1.2-.4t1.2.4l6 4.5q.375.275.588.7T20 10v9q0 .825-.588 1.413T18 21h-4q-.425 0-.712-.288T13 20v-5h-2v5q0 .425-.288.713T10 21H6q-.825 0-1.412-.587T4 19m8-6.75" />
            </svg>
        </a>
        <h2 class="text-lg font-semibold tracking-widest truncate font-shangrila-caps">Recipes</h2>
        <div class="size-6">
            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded">{{ count($this->recipes) }}</span>
        </div>
    </div>

    {{-- New Brewing Fixed Button --}}
    <div class="fixed bottom-8 right-8 shadow-xl">
        <a href="{{ route('fileUpload') }}" class="btn-yellow">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="M11 13H5v-2h6V5h2v6h6v2h-6v6h-2v-6Z" />
            </svg>
            <span>Import New Recipe</span>
        </a>
    </div>

    <div class="flex flex-col gap-y-4 px-3 pb-6 mt-8 overflow-x-hidden">
        @foreach ($this->recipes as $recipe)
        <a href="{{ route('recipes.show', $recipe) }}" class="bg-white shadow-md">
            <div class="max-w-full flex gap-4">
                <div class="flex flex-shrink-0 w-20">
                    <img class="object-cover bg-rich-black" src="/pictures/placeholder.webp">
                </div>
                <div class="flex flex-col truncate mr-4 py-2">
                    <h3 class="truncate text-lg font-bold sm:flex text-gray-900 uppercase">{{ $recipe->name }}</h3>
                    <h4 class="text-md text-gray-500 uppercase -mt-2">{{ $recipe->type }}</h4>
                    <div class="grid grid-cols-2 gap-x-2 gap-y-1 mt-2">
                        <div class="text-sm font-semibold">Volume</div>
                        <div class="info-label text-xs text-center">{{ $recipe->volume }} l</div>
                        <div class="text-sm font-semibold">Volume</div>
                        <div class="info-label text-xs text-center">{{ $recipe->volume }} l</div>
                    </div>
                    @php
                    $brewings = App\Models\Brewing::where('recipe_id', $recipe->id)->count();
                    @endphp
                    <div class="mt-2">
                        @if($brewings > 0)
                        <span class="bg-old-gold px-1.5 rounded font-semibold">{{ $brewings }}</span>
                        @else
                        <span class="text-sm text-gray-400">no </span>
                        @endif
                        <span class="text-sm text-gray-400">{{ Str::plural('brewing', $brewings) }} in progress</span>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
