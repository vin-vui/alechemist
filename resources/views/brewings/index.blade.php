<div class="pb-20">

    {{-- Header --}}
    <div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center shadow-lg z-30">
        <a href="{{ route('recipes.show', $recipe) }}">
            <svg class="size-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="m10.8 12l3.9 3.9q.275.275.275.7t-.275.7t-.7.275t-.7-.275l-4.6-4.6q-.15-.15-.212-.325T8.425 12t.063-.375t.212-.325l4.6-4.6q.275-.275.7-.275t.7.275t.275.7t-.275.7z" />
            </svg>
        </a>
        <h2 class="text-lg font-semibold tracking-widest truncate font-shangrila-caps">{{ Str::plural('Bewing', count($this->brewings)) }} In Progress</h2>
        <div class="size-6">
            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded font-semibold">{{ count($this->brewings) }}</span>
        </div>
    </div>

    <div class="bg-white relative z-20">
        <div class="max-w-full flex gap-4">
            <div class="flex flex-shrink-0 w-20">
                <img class="object-cover bg-rich-black" src="/pictures/placeholder.webp">
            </div>
            <div class="flex flex-col truncate flex-1 mr-4 py-2">
                <h3 class="truncate text-lg font-bold sm:flex text-gray-900 uppercase">{{ $recipe->name }}</h3>
                <h4 class="text-sm text-gray-500 uppercase -mt-2">{{ $recipe->type }}</h4>
                <div class="grid grid-cols-2 gap-x-2 gap-y-1 mt-2">
                    <div class="text-sm font-semibold">Method</div>
                    <div class="text-xs text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ $recipe->method }}</div>
                    <div class="text-sm font-semibold">Volume</div>
                    <div class="text-xs text-gray-600 bg-gray-100 px-1 rounded flex items-center justify-center">{{ $recipe->volume }} l</div>
                </div>
            </div>
        </div>
    </div>

    {{-- New Brewing Fixed Button --}}
    <div class="fixed bottom-8 right-8">
        <a href="{{ route('brewing.create', $recipe) }}" class="btn-yellow">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="M11 13H5v-2h6V5h2v6h6v2h-6v6h-2v-6Z" />
            </svg>
            <span>New Brewing</span>
        </a>
    </div>

    <div class="flex flex-col gap-y-4 mt-8 px-3">
        @foreach ($this->brewings as $brewing)
        <div class="flex bg-gray-50 shadow-md">
            @if ($brewing->current_step != null)
            <a href="{{ route($brewing->current_step, [$recipe, $brewing]) }}" class="flex flex-col w-full">
                @else
                <a href="{{ route('preparation', [$recipe, $brewing]) }}" class="flex flex-col w-full">
                    @endif
                    <div class="flex justify-start items-center">
                        <div class="flex shrink-0 w-1/4">
                            <img class="h-32 object-cover" src="/pictures/brew.jpg">
                        </div>
                        <div class="flex flex-col gap-y-2 w-full px-2">
                            <div class="flex">
                                <h3 class="text-lg font-semibold sm:flex text-gray-900 uppercase">{{ $brewing->name }}</h3>
                            </div>
                            <div class="-mt-4">
                                <span class="text-sm text-gray-500 uppercase -mt-2">{{ Carbon\Carbon::parse($brewing->date_start)->format('d/m/Y') }}</span>
                            </div>
                            @if($brewing->current_step)
                            <div class="grid grid-cols-2 gap-x-2 gap-y-1">
                                <div class="text-sm font-semibold">Current step</div>
                                <div class="text-xs text-gray-900 bg-old-gold px-1 rounded flex items-center justify-center">{{ $brewing->current_step }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </a>
        </div>
        @endforeach
    </div>
</div>
