<div>

    <div class="flex items-center text-lg gap-x-2 py-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <path fill="currentColor"
                d="M7.33 18.33c-.83-1.16-.83-2.5-.83-3.83c1.67 1 3.33 2 4.17 3.17l.33.56v-2.28c-1.5-.9-2.92-1.82-3.67-2.87c-.83-1.16-.83-2.5-.83-3.83c1.67 1 3.33 2 4.17 3.17L11 13v-2.3c-1.5-.9-2.92-1.82-3.67-2.87C6.5 6.67 6.5 5.33 6.5 4c1.67 1 3.33 2 4.17 3.17c.1.14.19.29.27.45c-.17-.62-.28-1.2-.29-1.8c-.01-1.51.65-3.06 1.31-4.61c.69 1.48 1.38 2.97 1.39 4.48c.01.63-.1 1.27-.28 1.9c.08-.14.16-.28.26-.42C14.17 6 15.83 5 17.5 4c0 1.33 0 2.67-.83 3.83C15.92 8.88 14.5 9.8 13 10.7V13l.33-.58c.84-1.17 2.5-2.17 4.17-3.17c0 1.33 0 2.67-.83 3.83c-.75 1.05-2.17 1.97-3.67 2.87v2.28l.33-.56c.84-1.17 2.5-2.17 4.17-3.17c0 1.33 0 2.67-.83 3.83c-.75 1.05-2.17 1.97-3.67 2.87V23h-2v-1.8c-1.5-.9-2.92-1.82-3.67-2.87Z" />
        </svg>
        <span class="border-b-old-gold border-0 border-b-2 w-1/5">Preparation</span>
    </div>
    <div class="flex items-center text-md gap-x-2 py-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="rounded-full bg-blue-500 p-1" width="28" height="28"
            viewBox="0 0 512 512">
            <path fill="white"
                d="M501.54 60.91c17.22-17.22 12.51-46.25-9.27-57.14a35.696 35.696 0 0 0-37.37 3.37L251.09 160h151.37l99.08-99.09zM496 192H16c-8.84 0-16 7.16-16 16v32c0 8.84 7.16 16 16 16h16c0 80.98 50.2 150.11 121.13 178.32c-12.76 16.87-21.72 36.8-24.95 58.69c-1.46 9.92 6.04 18.98 16.07 18.98h223.5c10.03 0 17.53-9.06 16.07-18.98c-3.22-21.89-12.18-41.82-24.95-58.69C429.8 406.11 480 336.98 480 256h16c8.84 0 16-7.16 16-16v-32c0-8.84-7.16-16-16-16z" />
        </svg>
        <span class="font-semibold">Concassez les grains</span>
    </div>
    <div class="flex flex-col">
        @foreach ($steps as $step)
            <div class="flex gap-x-4 items-center">
                    {{ $step->quantity }}
                    {{ $step->unit }}
                    {{ $step->field }}
            </div>
        @endforeach
    </div>
    <a href={{ route('mash', $brewing) }}>
        <button
            class="bg-xanthous rounded hover:bg-tawny hover:text-white transition-all duration-300 py-1.5 px-3 flex items-center gap-2">
            Next step
        </button>
    </a>
</div>
