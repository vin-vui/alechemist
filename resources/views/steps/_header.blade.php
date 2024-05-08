@php
$recipe = App\Models\Recipe::find($recipe);
@endphp

<div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center shadow-lg z-30">
    <a href="{{ route('recipes.show', $recipe->id) }}">
        <svg class="size-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path fill="currentColor" d="m10.8 12l3.9 3.9q.275.275.275.7t-.275.7t-.7.275t-.7-.275l-4.6-4.6q-.15-.15-.212-.325T8.425 12t.063-.375t.212-.325l4.6-4.6q.275-.275.7-.275t.7.275t.275.7t-.275.7z" />
        </svg>
    </a>
    <h2 class="text-lg font-semibold tracking-widest truncate font-shangrila-caps">{{ $brewing->name }}</h2>
    <div class="size-6"></div>
</div>

{{-- Informations --}}
<div class="bg-white relative z-20">
    <div class="max-w-full flex gap-4">
        <div class="flex flex-shrink-0 w-20">
            <img class="object-cover bg-rich-black" src="/pictures/placeholder.webp">
        </div>
        <div class="flex flex-col truncate flex-1 mr-4 py-2">
            <h3 class="truncate text-lg font-bold sm:flex text-gray-900 uppercase">{{ $recipe->name }}</h3>
            <h4 class="text-sm text-gray-500 uppercase -mt-2">{{ $recipe->type }}</h4>
            <div class="w-max grid grid-cols-2 gap-x-2 gap-y-1 mt-2">
                <div class="text-sm font-semibold">Started at</div>
                <div class="text-xs text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ Carbon\Carbon::parse($brewing->date_start)->format('d/m/Y') }}</div>
                <div class="text-sm font-semibold">Volume</div>
                <div class="text-xs text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ $recipe->volume }} l</div>
            </div>
        </div>
    </div>
</div>

<div class="fixed bottom-8 right-8">
    <x-note-button />
</div>
