<div>

    <div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center shadow-lg z-30">
        <button wire:click="back">
            <svg class="size-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="m10.8 12l3.9 3.9q.275.275.275.7t-.275.7t-.7.275t-.7-.275l-4.6-4.6q-.15-.15-.212-.325T8.425 12t.063-.375t.212-.325l4.6-4.6q.275-.275.7-.275t.7.275t.275.7t-.275.7z"/>
            </svg>
        </button>
        <h2 class="text-xl font-semibold tracking-widest truncate">{{ $brewing->name }}</h2>
        <div class="size-6"></div>
    </div>

    {{-- Informations --}}
    <div class="bg-white relative z-20">
        <div class="flex gap-4 items-center">
            <div class="shrink-0">
                <img class="h-32 object-cover" src="/pictures/recipe.jpg">
            </div>
            <div class="w-3/4 mr-4">
                <h3 class="truncate mr-4 text-lg font-bold sm:flex text-gray-900 uppercase">{{ $recipe->name }}</h3>
                <h4 class="text-sm text-gray-500 uppercase -mt-2">{{ $recipe->type }}</h4>
                <div class="grid grid-cols-2 gap-x-2 gap-y-1 mt-2">
                    <div class="text-sm font-semibold">Started at</div>
                    <div class="text-xs text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ Carbon\Carbon::parse($brewing->date_start)->format('d/m/Y') }}</div>
                    <div class="text-sm font-semibold">Volume</div>
                    <div class="text-xs text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ $recipe->volume }} l</div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-6 bg-gray-100 p-4">

        <div class="">
            <label class="text-sm font-semibold text-gray-500" for="name">Notes</label>
            <textarea rows="15"
                class="flex items-center px-2 w-full bg-white mt-1 focus:outline-none focus:ring-2 focus:ring-old-gold focus:border-old-gold"
                name="note" wire:model="note">{{$this->brewing->note}}
            </textarea>
        </div>
        <div class="flex justify-between gap-x-8">
            <button wire:click="back" class="bg-transparent border-2 border-tawny transition-all duration-300 py-1.5 px-3 flex items-center gap-1 justify-center">
                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path fill="currentColor" d="m10.8 12l3.9 3.9q.275.275.275.7t-.275.7t-.7.275t-.7-.275l-4.6-4.6q-.15-.15-.212-.325T8.425 12t.063-.375t.212-.325l4.6-4.6q.275-.275.7-.275t.7.275t.275.7t-.275.7z"/>
                </svg>
                <span>Cancel</span>
            </button>
            <button wire:click="update" class="btn-yellow">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="m10 13.6l5.9-5.9q.275-.275.7-.275t.7.275t.275.7t-.275.7l-6.6 6.6q-.3.3-.7.3t-.7-.3l-2.6-2.6q-.275-.275-.275-.7t.275-.7t.7-.275t.7.275z"/></svg>
                <span>Update</span>
            </button>
        </div>
    </div>
</div>