<dialog id="{{ $p ? 'p'.$p->id : 'p-new' }}">
  <form method="POST" action="{{ $p ? route('admin.products.update', $p) : route('admin.products.store') }}">
    @csrf @if ($p) @method('PUT') @endif
    <h3>{{ $p ? 'Edit Product' : 'New Product' }}</h3>
    <div class="f"><label>Name</label><input name="name" required value="{{ $p->name ?? '' }}"></div>
    <div class="f"><label>Description</label><input name="description" value="{{ $p->description ?? '' }}"></div>
    <div class="f2">
      <div class="f"><label>Price (₱/kg)</label><input type="number" step="0.01" min="0" name="price" required value="{{ $p->price ?? '' }}"></div>
      <div class="f"><label>Stock (kg)</label><input type="number" step="0.01" min="0" name="stock_kg" required value="{{ $p->stock_kg ?? '' }}"></div>
    </div>
    <div class="f"><label>Image URL</label><input type="url" name="image_url" value="{{ $p->image_url ?? '' }}"></div>
    <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CANCEL</button><button class="btn">SAVE</button></div>
  </form>
</dialog>
