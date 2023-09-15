@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-rich-black']) }}>
    {{ $value ?? $slot }}
</label>
