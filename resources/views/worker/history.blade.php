@extends('layouts.admin')
@section('content')
<div class="fbar">
  <span class="label">Monitoring Archive</span>
  <form method="GET" class="fsel"><x-icon n="filter" s="16" />
    <select name="batch" onchange="this.form.submit()">
      <option value="">Show All Batches</option>
      @foreach ($batches as $b)<option value="{{ $b->id }}" @selected(request('batch') == $b->id)>{{ $b->code }}</option>@endforeach
    </select></form>
</div>
<div class="card tbl plain">
  <table>
    <tr><th>Photo</th><th>Batch</th><th>Date &amp; Time</th><th>Result</th><th>Worker Notes</th><th class="r">Action</th></tr>
    @forelse ($logs as $l)
      <tr>
        <td><div class="timg" @style(["background-image: url('".$l->photo_url."')" => $l->photo_url])></div></td>
        <td class="mono">{{ $l->batch->code }}</td>
        <td style="color:#475569">{{ $l->created_at->format('Y-m-d') }}</td>
        <td>
          <span class="hl {{ $l->result === 'healthy' ? '' : ($l->result === 'contaminated' ? 'bad' : 'warn') }}">{{ $l->result === 'attention' ? 'Attention' : $l->result_label }}</span>
          @if ($l->ai_line)<div class="ai">AI: {{ $l->ai_line }}</div>@elseif ($l->ai_error)<div class="ai off">AI review unavailable</div>@endif
        </td>
        <td><div class="note">"{{ $l->notes ?: 'No notes' }}"</div>@if ($l->loss)<span class="hl warn" style="margin-top:6px">{{ $l->loss->quantity }} lost · {{ $l->loss->reason_label }}</span>@endif</td>
        <td class="r">
          <button class="eye" data-view data-photo="{{ $l->photo_url }}" data-batch="{{ $l->batch->code }}" data-date="{{ $l->created_at->format('Y-m-d H:i') }}"
            data-notes="{{ $l->notes }}" data-watered="{{ $l->watered ? 'Yes' : 'No' }}" data-result="{{ $l->result }}" data-lost="{{ $l->loss ? $l->loss->quantity.' ('.$l->loss->reason_label.')' : 'None' }}" data-worker="{{ $l->user->name }}"
            data-ai="{{ trim(($l->ai_line ?? '').($l->ai_summary ? ($l->ai_line ? ' — ' : '').$l->ai_summary : '')) }}" data-ai-conf="{{ $l->ai_confidence }}">
            <x-icon n="eye" s="18" />VIEW</button>
        </td>
      </tr>
    @empty <tr><td colspan="6" class="empty">No monitoring records yet.</td></tr> @endforelse
  </table>
</div>
<dialog id="viewDlg">
  <h3 id="vTitle"></h3>
  <img id="vImg" alt="" style="width:100%;border-radius:8px;margin-bottom:14px">
  <p id="vMeta" class="label" style="font-size:11px"></p>
  <p id="vNotes" class="note" style="white-space:normal;max-width:none;margin-top:12px"></p>
  <p id="vAi" class="aibox" hidden></p>
  <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CLOSE</button></div>
</dialog>
@endsection
