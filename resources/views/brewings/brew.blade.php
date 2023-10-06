<div>
    {{-- <div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center">
        <h2 class="text-xl font-semibold tracking-widest">Brewing {{ $brewing->name}}</h2>
        <a href="{{route('brewing')}}">
            <button
                class="bg-xanthous rounded hover:bg-tawny hover:text-white transition-all duration-300 py-1.5 px-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M11 13H5v-2h6V5h2v6h6v2h-6v6h-2v-6Z" />
                </svg>
                Back to Brewings
            </button>
        </a>
    </div>
    <div class="flex flex-col gap-4 px-4 sm:px-6 lg:px-8 pt-8 pb-12">

        <div class="flex bg-gray-50 shadow-lg">
            <div class="flex gap-4 items-center">
                <div class="shrink-0">
                    <img class="h-28 object-cover"
                        src="https://images.pexels.com/photos/1267359/pexels-photo-1267359.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1">
                </div>
                <div class="w-3/4">
                    <h3 class="truncate mr-4 text-lg font-bold sm:flex text-gray-900 uppercase">
                        {{ $brewing->name }}</h3>
                    <h4 class="text-md text-gray-500 uppercase">{{ $brewing->type }}</h4>
                    <span
                        class="text-xs bg-xanthous py-0.5 px-1.5 rounded text-gray-900 uppercase">{{ $brewing->alcohol }}
                        %</span>
                </div>
                <div class="text-sm w-full bg-xanthous py-1 px-1.5 rounded text-gray-900 uppercase">
                    {{ $brewing->date_start }}
                </div>
            </div>
        </div>
    </div> --}}
    @include('steps.preparation')
</div>
