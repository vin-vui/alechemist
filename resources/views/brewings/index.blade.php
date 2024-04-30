<div>

    {{-- Header --}}
    <div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center shadow-lg z-30">
        <a href="{{ route('recipes.show', $recipe) }}">
            <svg class="size-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="m10.8 12l3.9 3.9q.275.275.275.7t-.275.7t-.7.275t-.7-.275l-4.6-4.6q-.15-.15-.212-.325T8.425 12t.063-.375t.212-.325l4.6-4.6q.275-.275.7-.275t.7.275t.275.7t-.275.7z"/>
            </svg>
        </a>
        <h2 class="text-xl font-semibold tracking-widest truncate">{{ Str::plural('Bewing', count($this->brewings)) }} In Progress</h2>
        <div class="size-6">
            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded font-semibold">{{ count($this->brewings) }}</span>
        </div>
    </div>

    {{-- New Brewing Fixed Button --}}
    <div class="fixed bottom-4 right-4">
        <a href="{{ route('brewing.create', $recipe) }}" class="btn-yellow">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="M11 13H5v-2h6V5h2v6h6v2h-6v6h-2v-6Z" />
            </svg>
            <span>New Brewing</span>
        </a>
    </div>

    <div class="flex flex-col gap-y-4">
        @foreach ($this->brewings as $brewing)
            <div class="flex bg-gray-50 shadow">
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
                            <h3 class="text-lg font-semibold sm:flex text-gray-900 uppercase">
                                {{ $brewing->name }}
                            </h3>
                        </div>
                        @if($brewing->current_step)
                        <div class="flex gap-1">
                            <div class="font-semibold">Current step</div>
                            <div class="text-gray-900 bg-old-gold px-1 rounded flex items-center justify-center">{{ $brewing->current_step }}</div>
                        </div>
                        @endif
                        <div class="">
                            <span class="text-xs bg-gray-100 flex-none text-gray-500 py-0.5 px-1.5 rounded">Started at {{ Carbon\Carbon::parse($brewing->date_start)->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
