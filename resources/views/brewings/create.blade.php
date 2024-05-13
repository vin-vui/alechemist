<div>

    {{-- Header --}}
    <div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center shadow-lg z-30">
        <a href="{{ route('recipes.show', $recipe) }}">
            <svg class="size-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="m10.8 12l3.9 3.9q.275.275.275.7t-.275.7t-.7.275t-.7-.275l-4.6-4.6q-.15-.15-.212-.325T8.425 12t.063-.375t.212-.325l4.6-4.6q.275-.275.7-.275t.7.275t.275.7t-.275.7z" />
            </svg>
        </a>
        <h2 class="text-lg font-semibold tracking-widest truncate font-shangrila-caps">{{ __('New brewing') }}</h2>
        <div class="size-6"></div>
    </div>

    <div class="bg-white shadow-lg relative z-20">
        <div class="max-w-full flex gap-4">
            <div class="flex flex-shrink-0 w-20">
                <img class="object-cover bg-rich-black" src="/pictures/placeholder.webp">
            </div>
            <div class="flex flex-col truncate flex-1 mr-4 py-2">
                <h3 class="truncate text-lg font-bold sm:flex text-gray-900 uppercase">{{ $recipe->name }}</h3>
                <h4 class="text-sm text-gray-500 uppercase -mt-2">{{ $recipe->type }}</h4>
                <div class="w-max grid grid-cols-2 gap-x-2 gap-y-1 mt-2">
                    <div class="text-sm font-semibold">{{ __('Method') }}</div>
                    <div class="text-xs text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ $recipe->method }}</div>
                    <div class="text-sm font-semibold">{{ __('Volume') }}</div>
                    <div class="text-xs text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ $recipe->volume }} l</div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-6 bg-gray-50 rounded p-4 relative z-10">
        <div class="">
            <label class="text-sm font-semibold text-gray-500" for="name">{{ __('Name') }}</label>
            <input type="text" name="name" wire:model="name" class="flex items-center px-2 w-full bg-white mt-1 focus:outline-none focus:ring-2 focus:ring-old-gold focus:border-old-gold">
        </div>
        <div class="">
            <label class="text-sm font-semibold text-gray-500" for="date_start">{{ __('Start Date') }}</label>
            <input type="date" name="date_start" wire:model="date_start" class="flex items-center px-2 w-full bg-white mt-1 focus:outline-none focus:ring-2 focus:ring-old-gold focus:border-old-gold">
        </div>
        <div class="">
            <label class="text-sm font-semibold text-gray-500" for="note">{{ __('Notes') }}</label>
            <textarea rows="8" name="note" wire:model="note" class="flex items-center px-2 w-full bg-white mt-1 focus:outline-none focus:ring-2 focus:ring-old-gold focus:border-old-gold"></textarea>
        </div>
    </div>

    <div class="flex justify-between gap-x-1 px-8 mt-2 fixed bottom-8 w-full">
        <a href="{{ route('recipes.show', $recipe) }}" class="bg-transparent border-2 border-tawny transition-all duration-300 py-1.5 px-3 flex items-center gap-1 justify-center">
            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="m10.8 12l3.9 3.9q.275.275.275.7t-.275.7t-.7.275t-.7-.275l-4.6-4.6q-.15-.15-.212-.325T8.425 12t.063-.375t.212-.325l4.6-4.6q.275-.275.7-.275t.7.275t.275.7t-.275.7z" />
            </svg>
            <span>{{ __('Cancel') }}</span>
        </a>
        <button wire:click="store" class="btn-yellow">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="currentColor" d="M17 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6.81c-.39-.66-.64-1.4-.74-2.16a2.994 2.994 0 0 1-1.87-3.81C9.61 13.83 10.73 13 12 13c.44 0 .88.1 1.28.29c2.29-1.79 5.55-1.7 7.72.25V7zm-2 6H5V5h10zm-2 8h4v-3l5 4.5l-5 4.5v-3h-4z" /></svg>
            <span>{{ __('Save & Start') }}</span>
        </button>
    </div>

</div>
