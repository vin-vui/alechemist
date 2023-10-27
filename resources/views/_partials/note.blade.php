<div>
    <div class="mt-4 px-4">
        <div class="flex flex-col  text-sm text-gray-500 uppercase bg-gray-100 rounded mt-4">

            <div class="flex gap-x-2 p-4 w-full bg-old-gold text-black font-semibold">
                <div for="name">Brewing</div>
                 <span>{{ $this->brewing->name }}</span>
            </div>

            <div class="p-4 w-full">
                <label for="note" class="block p-4 font-semibold text-gray-500">Note(s)
                </label>
                <textarea rows="8"
                    class="block p-2.5 w-full h-96 min-h-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-old-gold focus:border-old-gold"
                    name="note" wire:model="note">{{$this->brewing->note}}
                </textarea>
            </div>
            <div class="flex justify-center gap-x-6 mb-4">
                <button type="button" wire:click="update"
                    class="flex items-center justify-center h-8 px-2 w-36 bg-xanthous mt-8 rounded font-semibold text-sm text-black hover:bg-tawny">Save
                </button>
                <button type="button" wire:click="back"
                    class="flex items-center justify-center h-8 px-2 w-36 bg-xanthous mt-8 rounded font-semibold text-sm text-black hover:bg-tawny">Back
                </button>
            </div>
        </div>
    </div>
</div>