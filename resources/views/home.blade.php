<div class="flex flex-col m-4 gap-4">
    <a href="{{ route('recipes.index') }}">
        <div class="flex justify-between items-center px-4 py-2 bg-white shadow-lg">
            <div class="rounded-full p-2 bg-xanthous">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 16 16">
                    <path fill="currentColor"
                        d="M5 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V5.414a1.5 1.5 0 0 0-.44-1.06L9.647 1.439A1.5 1.5 0 0 0 8.586 1H5ZM4 3a1 1 0 0 1 1-1h3v2.5A1.5 1.5 0 0 0 9.5 6H12v7a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V3Zm7.793 2H9.5a.5.5 0 0 1-.5-.5V2.207L11.793 5ZM7 7.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5ZM7.5 9a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3ZM7 11.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5ZM5.5 8a.5.5 0 1 0 0-1a.5.5 0 0 0 0 1ZM6 9.5a.5.5 0 1 1-1 0a.5.5 0 0 1 1 0ZM5.5 12a.5.5 0 1 0 0-1a.5.5 0 0 0 0 1Z" />
                </svg>
            </div>
            <div class="font-bold text-3xl">
                Recettes
            </div>
            <div class="font-semibold text-4xl">
                {{ $this->recipes_count }}
            </div>
        </div>
    </a>
    <div class="shadow-lg">
        <div class="flex justify-between items-center px-4 py-2 capitalize font-semibold bg-old-gold">
            ferment in progress
        </div>
        <div class="flex flex-col w-full mt-4 gap-y-4">
            @foreach ($this->fermentRecipes as $recipe)
                @foreach ($recipe->brewing as $brewing)
                    @if ($brewing->current_step == 'ferment')
                        <a href="{{ route('ferment', [$recipe, $brewing]) }}">
                            <div class="flex p-4 w-full bg-gray-50 shadow-lg">
                                <div class="flex w-full flex-col">
                                    <div class="flex flex-col">
                                        <div class="text-xl font-semibold">{{ $recipe->name }}</div>
                                        <div class="text-gray-500">{{ $brewing->name }}</div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between py-4">
                                            @php
                                                $totalTime = 0;
                                            @endphp
                                            @foreach ($brewing->BrewingSteps as $brewingStep)
                                                @if (
                                                    $brewingStep->type == 'Primary' ||
                                                        $brewingStep->type == 'Secondary' ||
                                                        $brewingStep->type == 'Tertiary' ||
                                                        $brewingStep->type == 'Bottle')
                                                    @php
                                                        $totalTime = $totalTime + $brewingStep->time;
                                                        $time_left = now()->diffInDays(Carbon\Carbon::create($brewing->ferment_start)->addMinutes($totalTime), false);
                                                        $time_left_min = now()->diffInMinutes(now()->addDays($time_left));
                                                        $time = (1 - ($time_left_min / $totalTime)) * 100;
                                                    @endphp
                                                    @if ($time_left > 0)
                                                        <div class="flex flex-col justify-items-center">
                                                            <div>{{ $brewingStep->type }}</div>
                                                            <div>{{ $time_left }} days left</div>
                                                            <div class="bg-gray-500 h-2">
                                                                <div style="width: {{ $time }}%" class="bg-xanthous h-2"></div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>
</div>
