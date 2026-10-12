<dialog id="{{ $b ? 'b'.$b->id : 'b-new' }}">
  <form method="POST" action="{{ $b ? route('admin.batches.update', $b) : route('admin.batches.store') }}">
    @csrf @if ($b) @method('PUT') @endif
    <h3>{{ $b ? 'Edit '.$b->code : 'New Batch' }}</h3>
    <div class="f2">
      <div class="f"><label>Batch ID</label><input name="code" value="{{ $b->code ?? '' }}" placeholder="Auto"></div>
      <div class="f"><label>Status</label>
        <select name="status">@foreach (['incubating','fruiting','harvested'] as $s)<option value="{{ $s }}" @selected(($b->status ?? 'incubating') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
    </div>
    <div class="f"><label>Name</label><input name="name" required value="{{ $b->name ?? '' }}"></div>
    <div class="f2">
      <div class="f"><label>Healthy bags</label><input type="number" min="0" name="bags" required value="{{ $b->bags ?? 0 }}"></div>
      <div class="f"><label>Lost bags <small style="font-weight:400">(workers report these in the daily check; raising it here is logged as an admin adjustment)</small></label><input type="number" min="0" name="lost_bags" required value="{{ $b->lost_bags ?? 0 }}"></div>
      <div class="f"><label>Yield (kg)</label><input type="number" step="0.01" min="0" name="yield_kg" value="{{ $b->yield_kg ?? 0 }}"></div>
      <div class="f"><label>Harvest date</label><input type="date" name="harvest_date" value="{{ optional($b->harvest_date ?? null)->format('Y-m-d') }}"></div>
    </div>
    <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CANCEL</button><button class="btn">SAVE</button></div>
  </form>
  @if ($b)
    <form class="del" method="POST" action="{{ route('admin.batches.destroy', $b) }}" data-confirm="Delete this batch? Its monitoring logs and harvest records will be deleted too." data-title="Delete batch" data-yes="DELETE" data-no="CANCEL" data-tone="danger">@csrf @method('DELETE')<button>DELETE BATCH</button></form>
  @endif
</dialog>
