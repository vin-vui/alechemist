<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 text-rich-black hover:text-white bg-xanthous border border-transparent font-semibold text-xs text-licorice uppercase tracking-widest hover:bg-tawny focus:bg-tawny active:bg-tawny focus:outline-none focus:ring-2 focus:ring-tawny focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
