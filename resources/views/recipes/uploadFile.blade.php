<x-app-layout>
    <div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center">
        <h2 class="text-xl font-semibold tracking-widest">Import File</h2>
    </div>
    <div class="max-w-xl mx-auto p-4">
        <form class="flex flex-col text-sm  bg-white rounded shadow-lg py-4"
        action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @error('file')<span class="flex justify-center text-xs text-tawny">{{ $message }} </span> @enderror
            <div class="flex flex-col justify-center items-center py-4 mx-4 gap-y-4">
                <label class="mb-2 text-lg font-medium text-gray-900" for="file">File BeerXML</label>
                <input class="w-full text-sm text-gray-900 border rounded cursor-pointer transition-all duration-300 file:bg-old-gold file:rounded file:border-0 file:p-2 hover:file:bg-tawny focus:outline-none" name="file" id="file" type="file">

            </div>

            <div class="flex justify-center gap-x-8">
                <button
                    type="submit" value="submit" name="action" class="mt-4 bg-xanthous rounded hover:bg-tawny hover:text-white transition-all duration-300 py-2 px-3 flex items-center gap-2">Submit
                </button>
                <button
                    type="submit" value="cancel" name="action" class="mt-4 bg-xanthous rounded hover:bg-tawny hover:text-white transition-all duration-300 py-2 px-3 flex items-center gap-2">Cancel
                </button>
            </div>

        </form>
    </div>
</x-app-layout>
