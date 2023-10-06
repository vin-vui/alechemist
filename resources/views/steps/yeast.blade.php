<div>
    <div class="flex items-center text-lg gap-x-2 py-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <path fill="currentColor"
                d="M21 13h-6.6l4.7 4.7l-1.4 1.4l-4.7-4.7V21h-2v-6.7L6.3 19l-1.4-1.4L9.4 13H3v-2h6.6L4.9 6.3l1.4-1.4L11 9.6V3h2v6.4l4.6-4.6L19 6.3L14.3 11H21v2Z" />
        </svg>
        <span class="border-b-old-gold border-0 border-b-2 w-1/5">Yeasts</span>
    </div>
    <div class="flex flex-col ml-12">
        @foreach ($steps as $step)
            <div class="flex gap-x-4 items-center">
                {{ $step->quantity }}
                {{ $step->unit }}
                {{ $step->field }}
            </div>
        @endforeach
    </div>
    <a href={{ route('ferment', $this->brewing) }}>
        <button
            class="bg-xanthous rounded hover:bg-tawny hover:text-white transition-all duration-300 py-1.5 px-3 flex items-center gap-2">
            Next step
        </button>
    </a>
</div>
