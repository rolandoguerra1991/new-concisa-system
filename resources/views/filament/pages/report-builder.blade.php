<x-filament-panels::page>
    <div class="grid grid-cols-2 gap-4">
        <x-filament::section>
            <x-slot name="heading">
                Ordenamiento de subcategorias
            </x-slot>
            <x-slot name="description">
                Configura el orden en el que se mostraran las subcategorias en los reportes.
            </x-slot>
            <x-filament::link
                :href="route('filament.app.resources.sub-category-sorts.index')"
                icon="heroicon-m-arrow-long-right"
                icon-position="after">
                Ir a la configuracion
            </x-filament::link>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Imagenes de fondo
            </x-slot>

            <x-slot name="description">
                Configura las imagenes de fondo que se mostraran en los reportes.
            </x-slot>
            <x-filament::link
                href="#"
                icon="heroicon-m-arrow-long-right"
                icon-position="after">
                Ir a la configuracion
            </x-filament::link>
        </x-filament::section>
    </div>
</x-filament-panels::page>
