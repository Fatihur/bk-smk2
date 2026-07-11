@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 rounded-lg shadow-sm']) }}>
