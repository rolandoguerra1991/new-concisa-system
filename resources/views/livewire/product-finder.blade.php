<div class="space-y-4">
    {{ $this->form }}

    <div class="space-y-4">
        @foreach ($products as $product)
            @livewire('product-finder-item', ['product' => $product], key($product->id))
        @endforeach
    </div>
</div>
