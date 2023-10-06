
<div>
    @include('steps.preparation')
    @include('steps.mash')
    @include('steps.boil')
    @include('steps.yeast')
    @include('steps.ferment')
</div>

    {{-- Brewing : {{ $recipe->name }}
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
        @foreach ($steps as $index => $step)
            @if ($currentStepType == 'Preparation')
                <div wire:key="step-{{ $index }}"
                    class="flex gap-x-4 items-center {{ $index <= $currentStep ? 'block' : 'hidden' }}">
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
    </div> --}}
    {{-- <div>
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
    <div>
        <svg xmlns="http://www.w3.org/2000/svg" class="rounded-full bg-cyan-500 p-1" width="28" height="28" viewBox="0 0 384 512"><path fill="white" d="M32 0C14.3 0 0 14.3 0 32s14.3 32 32 32v11c0 42.4 16.9 83.1 46.9 113.1l67.8 67.9l-67.8 67.9C48.9 353.9 32 394.6 32 437v11c-17.7 0-32 14.3-32 32s14.3 32 32 32h320c17.7 0 32-14.3 32-32s-14.3-32-32-32v-11c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1V64c17.7 0 32-14.3 32-32s-14.3-32-32-32H32zm256 437v11H96v-11c0-25.5 10.1-49.9 28.1-67.9l67.9-67.8l67.9 67.9c18 18 28.1 42.4 28.1 67.9z"/>
        </svg>
    </div> --}}
    {{-- <div class="flex items-center text-lg gap-x-2 py-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <path fill="currentColor"
                d="M21 12s-8.5-2-8.5-10c0 0 8.5 0 8.5 10M3 12C3 2 11.5 2 11.5 2c0 8-8.5 10-8.5 10m9-5.5s1 2.16 3 4c-.24 3.66-3 5.5-3 5.5s-2.76-1.84-3-5.5c2-1.84 3-4 3-4m8.75 6.75S20 17 18 19c0 0-2.47-1.64-3.67-4.19c.72-1.23 1.17-2.69 1.42-3.68c1.38 1.05 3 1.87 5 2.12m-5.25 5c-1 2-3.5 3.5-3.5 3.5s-2.5-1.5-3.5-3.5c0 0 1.09-.91 1.85-2.45c.47.55 1.01.99 1.65 1.2c.64-.21 1.18-.65 1.65-1.2c.76 1.54 1.85 2.45 1.85 2.45m-12.25-5c2-.25 3.62-1.07 5-2.12c.25.99.7 2.45 1.42 3.68C8.47 17.36 6 19 6 19c-2-2-2.75-5.75-2.75-5.75Z" />
        </svg>
        <span class="border-b-old-gold border-0 border-b-2 w-1/5">Mash</span>
    </div>
    <div class="flex flex-col">
        @foreach ($steps as $index => $step)
            @if ($currentStepType == 'Mash')
                <div wire:key="step-{{ $index }}"
                    class="flex items-center text-sm {{ $index <= $currentStep ? 'block' : 'hidden' }}">
                        <div class="flex items-center">
                            <label for="field"></label>
                            <input class="bg-anti-flash-white border-none w-40 placeholder-black rounded focus:outline-none focus:ring-2"
                                disabled type="text" name="field" placeholder="{{ $step->field }}">
                        </div>
                        <div class="flex flex-col items-center">
                            <label class="font-semibold text-gray-500" for="quantity_brewing">Quantity</label>
                            <input class="bg-gray-50 w-24 h-8 rounded focus:outline-none focus:ring-2" type="number"
                                name="quantity_brewing">
                        </div>
                        <div class="flex flex-col items-center justify-center">
                            <label class="font-semibold text-gray-500" for="time">Minutes</label>
                            <input class="bg-anti-flash-white border-none w-16 placeholder-black rounded focus:outline-none focus:ring-2"
                                disabled type="text" name="time" placeholder="{{ $step->time }}">
                        </div>
                        <div class="flex items-center">
                            <input class="focus:ring-0" type="checkbox" id="check{{ $index }}"
                                name="check{{ $index }}" wire:click="toggleNextStep"
                                {{ $index === $currentStep ? '' : 'checked' }}>
                        </div>
                </div>
            @endif
        @endforeach
    </div> --}}
    {{-- <div class="flex flex-col ml-12">
        @foreach ($steps as $index => $step)
            @if ($currentStepType == 'Mash')
                <div wire:key="step-{{ $index }}" class="flex gap-x-4 items-center {{ $index <= $currentStep ? 'block' : 'hidden' }}">
                    {{ $step->field }}
                    {{ $step->quantity }}
                    {{ $step->unit }}
                    {{ $step->time }}
                    <input class="focus:ring-0" type="checkbox" id="check{{ $index }}"
                        name="check{{ $index }}" wire:click="toggleNextStep"
                        {{ $index === $currentStep ? '' : 'checked' }}>
                    <label for="check{{ $index }}"></label>
                </div>
            @endif
        @endforeach
    </div> --}}
    {{-- <div class="flex items-center text-lg gap-x-2 py-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <path fill="currentColor"
                d="M19 5c-1.11 0-2 .89-2 2v6.76c-.64.57-1 1.39-1 2.24c0 1.66 1.34 3 3 3s3-1.34 3-3c0-.85-.36-1.67-1-2.23V7c0-1.11-.89-2-2-2m0 1c.55 0 1 .45 1 1v1h-2V7c0-.55.45-1 1-1M8 3.54l-.75.84S5.97 5.83 4.68 7.71S2 11.84 2 14c0 3.31 2.69 6 6 6s6-2.69 6-6c0-2.16-1.39-4.41-2.68-6.29S8.75 4.38 8.75 4.38L8 3.54m0 3.13c.44.52.84.95 1.68 2.17C10.89 10.6 12 12.84 12 14c0 2.22-1.78 4-4 4s-4-1.78-4-4c0-1.16 1.11-3.4 2.32-5.16C7.16 7.62 7.56 7.19 8 6.67Z" />
        </svg>
        <span class="border-b-old-gold border-0 border-b-2 w-1/5">Boil</span>
    </div>
    <div class="flex flex-col ml-12">
        @foreach ($steps as $index => $step)
            @if ($currentStepType == 'Boil')
                <div wire:key="step-{{ $index }}"
                    class="flex gap-x-4 items-center {{ $index <= $currentStep ? 'block' : 'hidden' }}">
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
    </div> --}}
    {{-- <div class="flex items-center text-lg gap-x-2 py-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
            <path fill="currentColor"
                d="M21 13h-6.6l4.7 4.7l-1.4 1.4l-4.7-4.7V21h-2v-6.7L6.3 19l-1.4-1.4L9.4 13H3v-2h6.6L4.9 6.3l1.4-1.4L11 9.6V3h2v6.4l4.6-4.6L19 6.3L14.3 11H21v2Z" />
        </svg>
        <span class="border-b-old-gold border-0 border-b-2 w-1/5">Yeasts</span>
    </div>
    <div class="flex flex-col ml-12">
        @foreach ($steps as $index => $step)
            @if ($currentStepType == 'Yeast')
                <div wire:key="step-{{ $index }}"
                    class="flex gap-x-4 items-center {{ $index <= $currentStep ? 'block' : 'hidden' }}">
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
    <div class="flex items-center text-lg gap-x-2 py-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512">
            <path fill="currentColor"
                d="M200 23v18h21.895l-14.31 123.303c-14.473 8.144-25.962 16.414-34.18 25.265c-9.02 9.712-14.405 20.57-14.405 31.97V445.54c0 11.4 5.042 21.877 12.348 29.794c7.305 7.917 17.208 13.666 28.35 13.666H312c11.23 0 21.24-5.72 28.596-13.645C347.953 467.432 353 456.94 353 445.54v-224c0-11.402-5.386-22.26-14.404-31.972c-8.22-8.85-19.708-17.12-34.18-25.265L290.106 41H312V23H200zm40.016 18h31.968l8.094 69.727c-2.328-.97-4.98-1.573-8.078-1.573c-10.342 0-17.062 6.425-22.15 10.772c-5.09 4.346-5.982 7.135-9.85 6.46c-4.685-.82-6.447-6.444-8.57-11.41L240.016 41zm31.25 86.113c.235.003.48.016.734.04c5.087.508 7.665 5.963 11.2 10.476l1.212 10.438a9.6 10.338 0 0 0-9.213-7.453a9.6 10.338 0 0 0-9.6 10.338a9.6 10.338 0 0 0 9.6 10.338a9.6 10.338 0 0 0 9.585-10.01l2.817 24.265l4.13 2.225c15.45 8.318 26.69 16.527 33.672 24.046c6.982 7.52 9.596 13.893 9.596 19.723v42.69h-25.568A64 94.77 0 0 0 256 221.54a64 94.77 0 0 0-53.416 42.69H177v-42.69c0-5.83 2.614-12.204 9.596-19.724s18.223-15.728 33.672-24.046l4.13-2.225l4.047-34.856c3.09 2.163 6.88 3.695 11.555 3.695c10.237 0 16.543-6.503 21.54-10.772c4.686-4.002 6.196-6.534 9.726-6.5zM256 166.4a9.6 10.338 0 0 0-9.6 10.338a9.6 10.338 0 0 0 9.6 10.338a9.6 10.338 0 0 0 9.6-10.338A9.6 10.338 0 0 0 256 166.4zm19.2 15.57a9.6 10.338 0 0 0-9.6 10.337a9.6 10.338 0 0 0 9.6 10.338a9.6 10.338 0 0 0 9.6-10.338a9.6 10.338 0 0 0-9.6-10.338zM256 247c13.42 0 23.9 9.857 30.578 22.445c1.962 3.698 3.663 7.726 5.098 12.016H336v18h-40.156c.757 5.4 1.156 11.038 1.156 16.85c0 12.354-1.775 23.944-5.06 34.075H336v18h-52.535c-6.64 9.987-15.97 17.23-27.465 17.23c-11.496 0-20.825-7.243-27.465-17.23H176v-18h44.06c-3.285-10.13-5.06-21.72-5.06-34.076c0-5.812.4-11.45 1.156-16.85H176v-18h44.324c1.435-4.29 3.136-8.317 5.098-12.015C232.102 256.857 242.58 247 256 247zm0 18c-4.253 0-9.775 3.644-14.678 12.883c-4.902 9.24-8.322 23.063-8.322 38.426c0 15.362 3.42 29.183 8.322 38.422c4.903 9.24 10.425 12.883 14.678 12.883s9.775-3.643 14.678-12.883c4.902-9.24 8.322-23.06 8.322-38.423c0-15.364-3.42-29.188-8.322-38.427C265.775 268.643 260.253 265 256 265zm-79 120.615h35.47a64 94.77 0 0 0 43.53 25.46a64 94.77 0 0 0 43.572-25.46H335v59.924c0 5.83-2.953 12.567-7.596 17.567c-4.643 5-10.635 7.893-15.404 7.893H199.697c-4.555 0-10.502-2.867-15.12-7.873c-4.62-5.006-7.577-11.758-7.577-17.588v-59.925z" />
        </svg>
        <span class="border-b-old-gold border-0 border-b-2 w-1/5">Ferment</span>
    </div>
    <div class="flex flex-col ml-12">
        @foreach ($steps as $index => $step)
            @if ($currentStepType == 'Primary' || $currentStepType == 'Secondary' || $currentStepType == 'Bottle')
                <div wire:key="step-{{ $index }}"
                    class="flex gap-x-4 items-center {{ $index <= $currentStep ? 'block' : 'hidden' }}">
                    {{ $step->time / 1440 }} jours
                    {{ $step->field }}
                    {{ $step->quantity }}
                    {{ $step->unit }}
                    <input class="focus:ring-0" type="checkbox" id="check{{ $index }}"
                        name="check{{ $index }}" wire:click="toggleNextStep"
                        {{ $index === $currentStep ? '' : 'checked' }}>
                    <label for="check{{ $index }}"></label>
                </div>
            @endif
        @endforeach
    </div> --}}
