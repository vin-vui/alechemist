<x-guest-layout>
    <div class="mt-12 max-w-xl mx-auto px-8">
        <form class="flex flex-col text-sm text-gray-500 uppercase bg-gray-50 rounded shadow-lg p-12 mt-12 dark:bg-gray-700 dark:text-gray-400"
        action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="py-4 flex justify-center items-center">
                <label class="text-lg font-semibold text-gray-500 @error('file') is-invalid @enderror" for="inputFile">File:</label>
                <input type="file" class="flex items-center h-8 px-4 w-62 bg-gray-50 opacity-75 mt-2 rounded focus:outline-none focus:ring-2" id="inputfile" name="file">
                @error('file')<span>{{ $message}} </span> @enderror
            </div>

            <div class="flex justify-center">
                <button
                    type="submit" class="flex items-center justify-center h-8 px-2 w-36 bg-blue-600 mt-8 rounded font-semibold text-sm text-blue-100 hover:bg-blue-700">Envoyer
                </button>
            </div>

        </form>
    </div>
</x-guest-layout>
