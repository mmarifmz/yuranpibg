@props(['title', 'value', 'color' => 'gray'])

<div class="bg-white rounded-2xl shadow p-4 text-center border-t-4 border-{{ $color }}-400">
    <h4 class="text-sm font-semibold text-gray-600">{{ $title }}</h4>
    <p class="text-2xl font-bold text-{{ $color }}-700 mt-1">{{ $value }}</p>
</div>