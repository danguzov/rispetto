@props([
    'label',
    'name',
    'options' => [],
    'value' => null,
    'required' => false,
    'placeholder' => null,
])

<div class="space-y-2">
    <label for="{{ $name }}" class="block text-sm font-medium text-rispetto-text">
        {{ $label }}
        @if ($required)
            <span class="text-rispetto-accent" aria-hidden="true">*</span>
        @endif
    </label>

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->class([
            'site-field',
            'border-red-500/60' => $errors->has($name),
        ]) }}
    >
        @if ($placeholder)
            <option value="" @selected(old($name, $value) === null || old($name, $value) === '') disabled>
                {{ $placeholder }}
            </option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(old($name, $value) === $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @error($name)
        <p class="text-sm text-red-400">{{ $message }}</p>
    @enderror
</div>
