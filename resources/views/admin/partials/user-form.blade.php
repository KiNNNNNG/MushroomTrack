<dialog id="{{ $u ? 'u'.$u->id : 'u-new' }}">
  <form method="POST" action="{{ $u ? route('admin.users.update', $u) : route('admin.users.store') }}">
    @csrf @if ($u) @method('PUT') @endif
    <h3>{{ $u ? 'Edit User' : 'Add New User' }}</h3>
    <div class="f"><label>Name</label><input name="name" required value="{{ $u->name ?? '' }}"></div>
    <div class="f"><label>Email</label><input type="email" name="email" required value="{{ $u->email ?? '' }}"></div>
    <div class="f2">
      <div class="f"><label>Role</label><select name="role">@foreach (['admin','worker','customer'] as $r)<option value="{{ $r }}" @selected(($u->role ?? 'worker') === $r)>{{ ucfirst($r) }}</option>@endforeach</select></div>
      @if ($u)<div class="f"><label>Status</label><select name="status">@foreach (['active','inactive'] as $s)<option value="{{ $s }}" @selected($u->status === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>@endif
    </div>
    <div class="f"><label>Password {{ $u ? '(leave blank to keep)' : '' }}</label><input type="password" name="password" minlength="8" @required(! $u)></div>
    <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CANCEL</button><button class="btn">SAVE</button></div>
  </form>
  @if ($u && $u->id !== auth()->id())
    <form class="del" method="POST" action="{{ route('admin.users.destroy', $u) }}" data-confirm="Delete this user?" data-title="Delete user" data-yes="DELETE" data-no="CANCEL" data-tone="danger">@csrf @method('DELETE')<button>DELETE USER</button></form>
  @endif
</dialog>
