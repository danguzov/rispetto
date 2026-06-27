@props([
    'label',
    'name',
    'value' => null,
    'rows' => 4,
])

<div class="space-y-2">
    <label for="{{ $name }}" class="block text-sm font-medium text-rispetto-text">
        {{ $label }}
    </label>

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->class([
            'site-field site-field--textarea',
            'border-red-500/60' => $errors->has($name),
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="text-sm text-red-400">{{ $message }}</p>
    @enderror
</div>
