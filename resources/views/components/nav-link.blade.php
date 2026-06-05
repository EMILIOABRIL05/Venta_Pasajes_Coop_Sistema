@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[#CC0000] text-sm font-medium leading-5 text-[#003366] focus:outline-none focus:border-[#CC0000] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-800 hover:text-[#003366] hover:border-[#CC0000]/40 focus:outline-none focus:text-[#003366] focus:border-[#CC0000]/60 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
