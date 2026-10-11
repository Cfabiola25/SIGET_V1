@props([
    'id' => 'siget-theme-toggle-' . uniqid(),
    'class' => '',
])

<div class="inline-flex items-center {{ $class }}">
    <label class="switch" title="Cambiar tema (Claro / Oscuro)">
        <input 
            class="switch__input theme-switch-checkbox" 
            type="checkbox" 
            role="switch" 
            id="{{ $id }}" 
            aria-label="Cambiar entre modo claro y modo oscuro" 
        />
        <span class="switch__icon">
            <span class="switch__icon-part switch__icon-part--1"></span>
            <span class="switch__icon-part switch__icon-part--2"></span>
            <span class="switch__icon-part switch__icon-part--3"></span>
            <span class="switch__icon-part switch__icon-part--4"></span>
            <span class="switch__icon-part switch__icon-part--5"></span>
            <span class="switch__icon-part switch__icon-part--6"></span>
            <span class="switch__icon-part switch__icon-part--7"></span>
            <span class="switch__icon-part switch__icon-part--8"></span>
            <span class="switch__icon-part switch__icon-part--9"></span>
            <span class="switch__icon-part switch__icon-part--10"></span>
            <span class="switch__icon-part switch__icon-part--11"></span>
        </span>
        <span class="switch__sr">Dark Mode</span>
    </label>
</div>
