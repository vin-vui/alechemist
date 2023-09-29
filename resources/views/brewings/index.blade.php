<div class="mx-4">
    Brewing : {{ $recipe->name }}
    <div class="flex items-center text-lg gap-x-2 py-4 ">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <path fill="currentColor"
                d="M7.33 18.33c-.83-1.16-.83-2.5-.83-3.83c1.67 1 3.33 2 4.17 3.17l.33.56v-2.28c-1.5-.9-2.92-1.82-3.67-2.87c-.83-1.16-.83-2.5-.83-3.83c1.67 1 3.33 2 4.17 3.17L11 13v-2.3c-1.5-.9-2.92-1.82-3.67-2.87C6.5 6.67 6.5 5.33 6.5 4c1.67 1 3.33 2 4.17 3.17c.1.14.19.29.27.45c-.17-.62-.28-1.2-.29-1.8c-.01-1.51.65-3.06 1.31-4.61c.69 1.48 1.38 2.97 1.39 4.48c.01.63-.1 1.27-.28 1.9c.08-.14.16-.28.26-.42C14.17 6 15.83 5 17.5 4c0 1.33 0 2.67-.83 3.83C15.92 8.88 14.5 9.8 13 10.7V13l.33-.58c.84-1.17 2.5-2.17 4.17-3.17c0 1.33 0 2.67-.83 3.83c-.75 1.05-2.17 1.97-3.67 2.87v2.28l.33-.56c.84-1.17 2.5-2.17 4.17-3.17c0 1.33 0 2.67-.83 3.83c-.75 1.05-2.17 1.97-3.67 2.87V23h-2v-1.8c-1.5-.9-2.92-1.82-3.67-2.87Z" />
        </svg>
        <span class="border-b-old-gold border-0 border-b-2 w-1/4">Preparation</span>
    </div>
    <div class="flex items-center text-md gap-x-2 py-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="rounded-full bg-blue-500 p-1" width="28" height="28"
            viewBox="0 0 512 512">
            <path fill="white"
                d="M501.54 60.91c17.22-17.22 12.51-46.25-9.27-57.14a35.696 35.696 0 0 0-37.37 3.37L251.09 160h151.37l99.08-99.09zM496 192H16c-8.84 0-16 7.16-16 16v32c0 8.84 7.16 16 16 16h16c0 80.98 50.2 150.11 121.13 178.32c-12.76 16.87-21.72 36.8-24.95 58.69c-1.46 9.92 6.04 18.98 16.07 18.98h223.5c10.03 0 17.53-9.06 16.07-18.98c-3.22-21.89-12.18-41.82-24.95-58.69C429.8 406.11 480 336.98 480 256h16c8.84 0 16-7.16 16-16v-32c0-8.84-7.16-16-16-16z" />
        </svg>
        <span class="font-semibold">Concassez les grains</span>
    </div>
    <div class="flex flex-col ml-12">
        @foreach ($steps as $index => $step)
            @if ($currentStepType == 'Preparation')
                <div wire:key="step-{{ $index }}" class="flex gap-x-4 items-center {{ $index <= $currentStep ? 'block' : 'hidden' }}">
                    {{ $step->quantity }}
                    {{ $step->unit }}
                    {{ $step->field }}
                    <input class="focus:ring-0" type="checkbox" id="check{{ $index }}"
                        name="check{{ $index }}" wire:click="toggleNextStep"
                        {{ $index === $currentStep ? '' : 'checked' }}>
                    <label for="check{{ $index }}"></label>
                </div>
            @endif
        @endforeach
    </div>
    <div>
        <svg xmlns="http://www.w3.org/2000/svg" class="rounded-full bg-red-500 p-1" width="28" height="28"
            viewBox="0 0 384 512">
            <path fill="white"
                d="m372.5 256.5l-.7-1.9C337.8 160.8 282 76.5 209.1 8.5l-3.3-3C202.1 2 197.1 0 192 0s-10.1 2-13.8 5.5l-3.3 3C102 76.5 46.2 160.8 12.2 254.6l-.7 1.9C3.9 277.3 0 299.4 0 321.6C0 426.7 86.8 512 192 512s192-85.3 192-190.4c0-22.2-3.9-44.2-11.5-65.1zM281.7 306a73.3 73.3 0 0 1 6.2 29.5c0 53-43 96.5-96 96.5s-96-43.5-96-96.5c0-10.1 2.1-20.3 6.2-29.5l1.9-4.3c15.8-35.4 37.9-67.7 65.3-95.1l8.9-8.9c3.6-3.6 8.5-5.6 13.6-5.6s10 2 13.6 5.6l8.9 8.9c27.4 27.4 49.6 59.7 65.3 95.1l1.9 4.3z" />
        </svg>
    </div>
    <div>
        <svg xmlns="http://www.w3.org/2000/svg" class="rounded-full bg-green-500 p-1" width="28" height="28"
            viewBox="0 0 512 512">
            <path fill="white"
                d="M480.1 31.9c-55-55.1-164.9-34.5-227.8 28.5c-49.3 49.3-55.1 110-28.8 160.4L9 413.2c-11.6 10.5-12.1 28.5-1 39.5L59.3 504c11 11 29.1 10.5 39.5-1.1l192.4-214.4c50.4 26.3 111.1 20.5 160.4-28.8c63-62.9 83.6-172.8 28.5-227.8z" />
        </svg>
    </div>
    <div>
        <svg xmlns="http://www.w3.org/2000/svg" class="rounded-full bg-cyan-500 p-1" width="28" height="28"
            viewBox="0 0 352 512">
            <path fill="white"
                d="M205.22 22.09c-7.94-28.78-49.44-30.12-58.44 0C100.01 179.85 0 222.72 0 333.91C0 432.35 78.72 512 176 512s176-79.65 176-178.09c0-111.75-99.79-153.34-146.78-311.82zM176 448c-61.75 0-112-50.25-112-112c0-8.84 7.16-16 16-16s16 7.16 16 16c0 44.11 35.89 80 80 80c8.84 0 16 7.16 16 16s-7.16 16-16 16z" />
        </svg>
    </div>
    <div class="text-lg font-semibold px-2 border-b-old-gold border-0 border-b-2 w-1/2">
        Mash
    </div>
    <div class="flex flex-col ml-12">
        @foreach ($steps as $index => $step)
            @if ($currentStepType == 'Mash')
                <div wire:key="step-{{ $index }}" class="flex gap-x-4 items-center {{ $index <= $currentStep ? 'block' : 'hidden' }}">
                    {{ $step->quantity }}
                    {{ $step->unit }}
                    {{ $step->field }}
                    <input class="focus:ring-0" type="checkbox" id="check{{ $index }}"
                        name="check{{ $index }}" wire:click="toggleNextStep"
                        {{ $index === $currentStep ? '' : 'checked' }}>
                    <label for="check{{ $index }}"></label>
                </div>
            @endif
        @endforeach
    </div>
    <div class="text-lg font-semibold px-2 border-b-old-gold border-0 border-b-2 w-1/2">
        Boil
    </div>
    <div class="text-lg font-semibold px-2 border-b-old-gold border-0 border-b-2 w-1/2">
        Ferment
    </div>
</div>
