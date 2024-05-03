<x-app-layout>

    {{-- Header --}}
    <div class="bg-white sticky sm:top-0 top-12 px-4 py-2.5 flex justify-between items-center shadow-lg z-30">
        <a href="{{ route('recipes.index') }}">
            <svg class="size-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="m10.8 12l3.9 3.9q.275.275.275.7t-.275.7t-.7.275t-.7-.275l-4.6-4.6q-.15-.15-.212-.325T8.425 12t.063-.375t.212-.325l4.6-4.6q.275-.275.7-.275t.7.275t.275.7t-.275.7z"/>
            </svg>
        </a>
        <h2 class="text-xl font-semibold tracking-widest truncate">Import File</h2>
        <div class="size-6"></div>
    </div>

    <div class="relative z-20 bg-white flex justify-center">
        <img class="size-48" src="/pictures/upload-hop.webp" alt="">
    </div>

    <div class="max-w-xl mx-auto relative z-10 mt-8">
        <form class="flex flex-col text-sm bg-white py-4" action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @error('file')<span class="flex justify-center text-xs text-tawny">{{ $message }} </span> @enderror
            <div class="flex flex-col justify-center items-center py-8 mx-4 gap-y-4">
                <label class="mb-2 text-lg font-medium text-gray-900" for="file">File BeerXML Format Only</label>
                <input class="w-full text-sm text-gray-900 cursor-pointer transition-all duration-300 file:bg-old-gold file:border-0 file:p-2 hover:file:bg-tawny focus:outline-none" name="file" id="file" type="file">
            </div>
            <div class="flex justify-between gap-x-8 px-4 mt-2">
                <a href="{{ route('recipes.index') }}" class="bg-transparent border-2 border-tawny transition-all duration-300 py-1.5 px-3 flex items-center gap-1 justify-center">
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path fill="currentColor" d="m10.8 12l3.9 3.9q.275.275.275.7t-.275.7t-.7.275t-.7-.275l-4.6-4.6q-.15-.15-.212-.325T8.425 12t.063-.375t.212-.325l4.6-4.6q.275-.275.7-.275t.7.275t.275.7t-.275.7z"/>
                    </svg>
                    <span>Cancel</span>
                </a>
                <button type="submit" value="submit" name="action" class="btn-yellow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M12 1.25a.75.75 0 0 1 .57.262l3 3.5a.75.75 0 1 1-1.14.976l-1.68-1.96V15a.75.75 0 0 1-1.5 0V4.027L9.57 5.988a.75.75 0 1 1-1.14-.976l3-3.5A.75.75 0 0 1 12 1.25M6.996 8.252a.75.75 0 0 1 .008 1.5c-1.093.006-1.868.034-2.457.142c-.566.105-.895.272-1.138.515c-.277.277-.457.666-.556 1.4c-.101.755-.103 1.756-.103 3.191v1c0 1.436.002 2.437.103 3.192c.099.734.28 1.122.556 1.4c.277.276.665.456 1.4.555c.754.102 1.756.103 3.191.103h8c1.435 0 2.436-.001 3.192-.103c.734-.099 1.122-.279 1.399-.556c.277-.277.457-.665.556-1.399c.101-.755.103-1.756.103-3.192v-1c0-1.435-.002-2.436-.103-3.192c-.099-.733-.28-1.122-.556-1.399c-.244-.243-.572-.41-1.138-.515c-.589-.108-1.364-.136-2.457-.142a.75.75 0 1 1 .008-1.5c1.082.006 1.983.032 2.72.167c.758.14 1.403.405 1.928.93c.602.601.86 1.36.982 2.26c.116.866.116 1.969.116 3.336v1.11c0 1.368 0 2.47-.116 3.337c-.122.9-.38 1.658-.982 2.26c-.602.602-1.36.86-2.26.982c-.867.116-1.97.116-3.337.116h-8.11c-1.367 0-2.47 0-3.337-.116c-.9-.121-1.658-.38-2.26-.982c-.602-.602-.86-1.36-.981-2.26c-.117-.867-.117-1.97-.117-3.337v-1.11c0-1.367 0-2.47.117-3.337c.12-.9.38-1.658.981-2.26c.525-.524 1.17-.79 1.928-.929c.737-.135 1.638-.161 2.72-.167" clip-rule="evenodd"/></svg>
                    <span>Upload</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
