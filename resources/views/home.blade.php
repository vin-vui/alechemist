<div class="pb-20">
    <div class="flex flex-col gap-8">

        <div class="">
            <a href="{{ route('recipes.index') }}">
                <div class="flex justify-between items-center px-4 py-2 bg-white">
                    <div class="flex items-center">
                        <img class="size-24" src="/pictures/recipe.webp" alt="">
                        <div class="font-bold text-3xl font-shangrila-caps">
                            {{ Str::plural(__('recipe'), count($this->recipes)) }}
                        </div>
                    </div>
                    <div class="font-semibold text-4xl bg-gray-100 py-1 px-2 rounded font-shangrila-caps">
                        {{ count($this->recipes) }}
                    </div>
                </div>
            </a>
        </div>

        <div class="">
            <div class="flex justify-between items-center px-4 py-2 capitalize font-semibold bg-old-gold sticky md:top-0 top-12 mb-6">
                <span>{{ Str::plural('fermentation', count($this->fermentRecipes)) }} {{ __('in progress') }}</span>
                <span class="info-label">{{ count($this->fermentRecipes) }}</span>
            </div>
            <div class="flex flex-col w-full gap-y-4 px-4">
                @if(count($this->fermentRecipes) > 0)
                @foreach ($this->fermentRecipes as $recipe)
                @foreach ($recipe->brewing as $brewing)
                @if ($brewing->current_step == 'ferment')
                <a href="{{ route('ferment', [$recipe, $brewing]) }}">
                    <div class="flex p-4 w-full bg-gray-50 shadow-md">
                        <div class="flex w-full flex-col">
                            <div class="flex flex-col">
                                <h3 class="truncate mr-4 text-lg font-bold sm:flex text-gray-900 uppercase">{{ $brewing->name }}</h3>
                                <h4 class="text-sm text-gray-500 uppercase -mt-2">{{ $recipe->name }}</h4>
                            </div>
                            <div>
                                <div class="flex flex-col md:flex-row justify-between py-4 gap-6">
                                    @php
                                    $totalTime = 0;
                                    @endphp
                                    @foreach ($brewing->BrewingSteps as $brewingStep)
                                    @if ($brewingStep->type == 'Primary' ||
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
                                    <div class="flex flex-col justify-items-center w-full">
                                        <div class="flex justify-between">
                                            <div class="">{{ $brewingStep->field }}</div>
                                            <div class="info-label-yellow text-sm">
                                                {{ $time_left <= 0 ? 'finished' : round($time_left) . ' ' . __('days left') }}
                                            </div>
                                        </div>
                                        <div class="bg-gray-500 h-2 my-1">
                                            <div style="width: {{ $time }}%" class="bg-xanthous h-2"></div>
                                        </div>
                                        <div class="flex justify-start">
                                            <div class="info-label text-xs">
                                                {{ __('ends') }} {{ Carbon\Carbon::create($brewing->ferment_start)->addMinutes($totalTime)->format('d/m/Y') }}
                                            </div>
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
                @else
                <div class="flex justify-center items-center bg-white shadow-md p-4 mb-8">
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-gray-900 uppercase font-shangrila-caps">No fermentations in progress</h3>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="flex justify-between items-center px-4 py-2 capitalize font-semibold bg-old-gold sticky md:top-0 top-12 mb-6">
                <span>{{ Str::plural(__('recipe'), count($this->progressBrewings)) }} {{ __('in progress') }}</span>
                <span class="info-label">{{ count($this->progressBrewings) }}</span>
            </div>
            <div class="flex flex-col w-full gap-y-4 px-4">
                @foreach ($this->progressBrewings as $brewing)
                <a href="{{ route($brewing->current_step, [$brewing->recipe, $brewing]) }}" class="bg-white shadow-md">
                    <div class="max-w-full flex gap-4">
                        <div class="flex flex-shrink-0 w-20">
                            <img class="object-cover bg-rich-black" src="/pictures/placeholder.webp">
                        </div>
                        <div class="flex flex-col truncate flex-1 mr-4 py-2">
                            <h3 class="truncate text-lg font-bold sm:flex text-gray-900 uppercase">{{ $brewing->name }}</h3>
                            <h4 class="text-sm text-gray-500 uppercase -mt-2">{{ $brewing->recipe->name }}</h4>
                            <div class="w-max grid grid-cols-2 gap-x-2 gap-y-1 mt-2">
                                <div class="text-sm font-semibold">{{ __('Started at') }}</div>
                                <div class="info-label text-xs text-center">{{ Carbon\Carbon::parse($brewing->date_start)->format('d/m/Y') }}</div>
                                <div class="text-sm font-semibold">{{ __('Volume') }}</div>
                                <div class="info-label text-xs text-center">{{ $brewing->recipe->volume }} l</div>
                                <div class="text-sm font-semibold">{{ __('Step') }}</div>
                                <div class="info-label-yellow text-xs font-semibold text-center">{{ __($brewing->current_step) }}</div>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

    </div>
