@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'autocomplete' => null,
    'inputmode' => null,
])

<div class="space-y-2">
    <label for="{{ $name }}" class="block text-sm font-medium text-rispetto-text">
        {{ $label }}
        @if ($required)
            <span class="text-rispetto-accent" aria-hidden="true">*</span>
        @endif
    </label>

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($inputmode) inputmode="{{ $inputmode }}" @endif
        {{ $attributes->class([
            'site-field',
            'border-red-500/60' => $errors->has($name),
        ]) }}
    />

    @error($name)
        <p class="text-sm text-red-400">{{ $message }}</p>
    @enderror
</div>
