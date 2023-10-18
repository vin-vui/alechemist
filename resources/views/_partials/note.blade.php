<div>
    <div class="mt-4 px-4">
        <div class="flex flex-col  text-sm text-gray-500 uppercase bg-gray-100 rounded mt-4">

            <div class="p-4 w-full">
                <label class="font-semibold text-gray-500" for="name">Name</label>
                <input type="text"
                    class="flex items-center h-8 px-2 w-full bg-gray-100 mt-2 rounded focus:outline-none focus:ring-2"
                    name="name" wire:model="name">
            </div>

            <div class="flex flex-col p-4 w-lg">
                <label class="font-semibold text-gray-500" for="date_start">Start Date</label>
                <input type="date"
                    class="flex items-center h-8 px-2 w-full bg-gray-100 mt-2 rounded focus:outline-none focus:ring-2"
                    name="date_start" wire:model="date_start">
            </div>
            <div class="p-4 w-full">
                <label for="note" class="block p-4 font-semibold text-gray-500">Note(s)
                </label>
                <textarea rows="8"
                    class="block p-2.5 w-full h-96 min-h-full text-sm text-gray-900 bg-gray-100 rounded-lg border border-gray-300 focus:ring-old-gold focus:border-old-gold"
                    name="note" wire:model="note">
                </textarea>
            </div>
            <div class="flex justify-center">
                <button type="button" wire:click="store"
                    class="flex items-center justify-center h-8 px-2 w-36 bg-old-gold mt-8 rounded font-semibold text-sm text-black hover:bg-tawny">Save
                </button>
            </div>
        </div>
    </div>
</div>