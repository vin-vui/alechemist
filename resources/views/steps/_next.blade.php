@if ($allChecked)
<button type="button" wire:click="next" class="bg-xanthous rounded shadow-lg hover:bg-tawny hover:text-white transition-all duration-300 py-4 flex items-center shrink-0 w-full justify-between font-semibold uppercase">
    <div></div>
    <div class="ml-16">Next step</div>
    <svg class="mr-16"xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 100 100">
        <path fill="currentColor" d="m50.868 78.016l36.418-26.055a2.516 2.516 0 0 0 1.051-2.043v-.006a2.52 2.52 0 0 0-1.059-2.048L50.86 21.977a2.513 2.513 0 0 0-2.612-.183a2.509 2.509 0 0 0-1.361 2.236v12.183l-32.709-.001a2.514 2.514 0 0 0-2.515 2.516l.001 22.541a2.515 2.515 0 0 0 2.516 2.516h32.706v12.187c0 .94.53 1.803 1.366 2.237a2.512 2.512 0 0 0 2.616-.193z" />
    </svg>
</button>
@else
@if($this->brewing->ferment_start != null)
<button type="button" class="bg-gray-100 rounded py-4 flex items-center shrink-0 w-full justify-between font-semibold uppercase">
    <div></div>
    <div class="ml-16">end of brewing</div>
    <svg class="mr-16" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="M2 20h2c.55 0 1-.45 1-1v-9c0-.55-.45-1-1-1H2zm19.83-7.12c.11-.25.17-.52.17-.8V11c0-1.1-.9-2-2-2h-5.5l.92-4.65c.05-.22.02-.46-.08-.66a4.8 4.8 0 0 0-.88-1.22L14 2L7.59 8.41C7.21 8.79 7 9.3 7 9.83v7.84A2.34 2.34 0 0 0 9.34 20h8.11c.7 0 1.36-.37 1.72-.97z"/></svg>
</button>
@else
<button type="button" class="bg-gray-100 rounded  py-4 flex items-center shrink-0 w-full justify-between font-semibold uppercase">
    <div></div>
    <div class="ml-16">Next step</div>
    <svg class="mr-16"xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 100 100">
        <path fill="currentColor" d="m50.868 78.016l36.418-26.055a2.516 2.516 0 0 0 1.051-2.043v-.006a2.52 2.52 0 0 0-1.059-2.048L50.86 21.977a2.513 2.513 0 0 0-2.612-.183a2.509 2.509 0 0 0-1.361 2.236v12.183l-32.709-.001a2.514 2.514 0 0 0-2.515 2.516l.001 22.541a2.515 2.515 0 0 0 2.516 2.516h32.706v12.187c0 .94.53 1.803 1.366 2.237a2.512 2.512 0 0 0 2.616-.193z" />
    </svg>
</button>
@endif
@endif