<dialog id="{{ $u ? 'u'.$u->id : 'u-new' }}" style="border: none; border-radius: 16px; padding: 32px; width: 480px; max-width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
  <form method="POST" action="{{ $u ? route('admin.users.update', $u) : route('admin.users.store') }}">
    @csrf @if ($u) @method('PUT') @endif
    <h3 style="font-size: 20px; font-weight: 800; color: #241c16; margin: 0 0 24px;">{{ $u ? 'Edit User' : 'Add New Worker' }}</h3>
    
    <div style="margin-bottom: 16px;">
      <label style="display: block; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">FULL NAME</label>
      <input name="name" required value="{{ $u->name ?? '' }}" placeholder="Juan Dela Cruz" style="width: 100%; height: 44px; border: 1px solid #e8e2d8; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #241c16; background: #faf8f5; box-sizing: border-box;">
    </div>

    <div style="margin-bottom: 16px;">
      <label style="display: block; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">EMAIL</label>
      <input type="email" name="email" required value="{{ $u->email ?? '' }}" placeholder="juan@mushfungai.com" style="width: 100%; height: 44px; border: 1px solid #e8e2d8; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #241c16; background: #faf8f5; box-sizing: border-box;">
    </div>

    <div style="margin-bottom: 16px;">
      <label style="display: block; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">PASSWORD{{ $u ? ' (leave blank to keep)' : '' }}</label>
      <input type="password" name="password" minlength="8" @required(!$u) placeholder="••••••••" style="width: 100%; height: 44px; border: 1px solid #e8e2d8; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #241c16; background: #faf8f5; box-sizing: border-box;">
    </div>

    <div style="margin-bottom: {{ $u ? '16px' : '24px' }};">
      <label style="display: block; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">ROLE</label>
      @if ($u)
        <select name="role" style="width: 100%; height: 44px; border: 1px solid #e8e2d8; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #241c16; background: #faf8f5; box-sizing: border-box;">
          @foreach (['worker' => 'Worker', 'admin' => 'Admin', 'customer' => 'Customer'] as $r => $label)
            <option value="{{ $r }}" @selected($u->role === $r)>{{ $label }}</option>
          @endforeach
        </select>
      @else
        <input type="hidden" name="role" value="worker">
        <input type="text" value="Worker (fixed)" disabled style="width: 100%; height: 44px; border: 1px solid #e8e2d8; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #6b5a4e; background: #f0ece5; box-sizing: border-box; cursor: not-allowed;">
      @endif
    </div>

    @if ($u)
      <div style="margin-bottom: 24px;">
        <label style="display: block; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">STATUS</label>
        <select name="status" style="width: 100%; height: 44px; border: 1px solid #e8e2d8; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #241c16; background: #faf8f5; box-sizing: border-box;">
          @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $s => $label)
            <option value="{{ $s }}" @selected($u->status === $s)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
    @endif

    <div style="display: flex; gap: 10px; justify-content: flex-end;">
      <button type="button" onclick="this.closest('dialog').close()" style="background: #fff; border: 1px solid #e8e2d8; border-radius: 8px; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer; color: #241c16;">CANCEL</button>
      <button type="submit" style="background: #205e46; color: #fff; border: none; border-radius: 8px; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer;">{{ $u ? 'SAVE' : 'ADD NEW WORKER' }}</button>
    </div>
  </form>

  @if ($u && $u->id !== auth()->id())
    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" data-confirm="Delete this user?" data-title="Delete user" data-yes="DELETE" data-no="CANCEL" data-tone="danger" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e8e2d8; text-align: center;">
      @csrf @method('DELETE')
      <button type="submit" style="background: none; border: none; color: #b91c1c; font-size: 13px; font-weight: 700; cursor: pointer;">DELETE USER</button>
    </form>
  @endif
</dialog>
