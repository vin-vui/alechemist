<x-app-layout>
    <div class="max-w-xl mx-auto py-4 px-4">
        <form class="flex flex-col text-sm  bg-gray-50 rounded shadow-lg py-4"
        action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @error('file')<span class="flex justify-center text-xs text-tawny">{{ $message }} </span> @enderror
            <div class="py-4 flex flex-col justify-center items-center">
                <label class="text-lg font-semibold  @error('file') is-invalid @enderror" for="inputFile">File BeerXML:</label>
                <input type="file" class="flex items-center h-8 px-4 w-62 bg-gray-50  mt-2 rounded focus:outline-none focus:ring-2" id="inputfile" name="file">
            </div>

            <div class="flex justify-center">
                <button
                    type="submit" class="bg-xanthous hover:bg-tawny hover:text-white transition-all duration-300 py-1.5 px-3 flex items-center gap-2">Submit
                </button>
            </div>

        </form>
    </div>
</x-app-layout>
