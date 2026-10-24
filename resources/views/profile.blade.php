@extends('layouts.admin')
@section('title', 'My Profile — MushFungAI')

@section('content')
@php
  $av = $u->avatar ? asset($u->avatar) : null;
  $roleLabel = match($u->role) {
    'admin' => 'Administrator',
    'worker' => 'Worker',
    default => 'Customer',
  };
@endphp

<div class="profile-container">

  {{-- Top Hero Card (matching media_1792515596873_4fdf3000.png) --}}
  <div class="profile-header-card">
    <div class="profile-header-left">
      <div class="profile-avatar-wrap">
        <div class="profile-avatar">
          @if ($av)
            <img src="{{ $av }}" alt="{{ $u->name }}">
          @else
            <span>{{ strtoupper(substr($u->name, 0, 1)) }}</span>
          @endif
        </div>

        <form id="avatarUploadForm" method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data">
          @csrf
          <input type="file" name="photo" id="profilePhotoFile" accept="image/*" style="display:none;" onchange="this.form.submit()">
          <button type="button" class="profile-avatar-btn" onclick="document.getElementById('profilePhotoFile').click()" title="Change profile photo" aria-label="Change photo">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
              <circle cx="12" cy="13" r="4"/>
            </svg>
          </button>
        </form>
      </div>

      <div class="profile-header-info">
        <div class="profile-header-name-row">
          <h1 class="profile-name">{{ $u->name }}</h1>
          <span class="profile-role-badge role-badge-{{ $u->role }}">{{ strtoupper($u->role) }}</span>
        </div>
        <div class="profile-member-since">Member since {{ $u->created_at->format('M Y') }}</div>
        @if ($av)
          <form method="POST" action="{{ route('profile.photo.remove') }}" style="display:inline;margin-top:6px;">
            @csrf
            @method('DELETE')
            <button type="submit" style="background:none;border:none;color:#b91c1c;font-size:11px;font-weight:700;cursor:pointer;padding:0;text-decoration:underline;">
              Remove photo
            </button>
          </form>
        @endif
      </div>
    </div>

    {{-- Far Right: Settings Button + Popover --}}
    <div class="profile-header-actions" style="position: relative;">
      <button type="button" class="profile-settings-btn" id="profileSettingsTrigger" onclick="toggleDisplaySettings(event)" title="Display settings" aria-label="Display settings">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="4" y1="21" x2="4" y2="14"/>
          <line x1="4" y1="10" x2="4" y2="3"/>
          <line x1="12" y1="21" x2="12" y2="12"/>
          <line x1="12" y1="8" x2="12" y2="3"/>
          <line x1="20" y1="21" x2="20" y2="16"/>
          <line x1="20" y1="12" x2="20" y2="3"/>
          <line x1="1" y1="14" x2="7" y2="14"/>
          <line x1="9" y1="8" x2="15" y2="8"/>
          <line x1="17" y1="16" x2="23" y2="16"/>
        </svg>
      </button>

      {{-- Display Settings Popover (matching media_1792515596831_2de054b8.png) --}}
      <div class="display-settings-popover" id="displaySettingsPopover">
        <div class="dsp-title">Display settings</div>
        
        {{-- Dark mode --}}
        <div class="dsp-row">
          <div class="dsp-row-left">
            <div class="dsp-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
              </svg>
            </div>
            <div>
              <div class="dsp-label">Dark mode</div>
              <div class="dsp-sub">Switch between light and dark theme.</div>
            </div>
          </div>
          <label class="dsp-switch">
            <input type="checkbox" id="themeToggleSwitch" onchange="toggleDarkTheme(this.checked)">
            <span class="dsp-slider"></span>
          </label>
        </div>

        {{-- Font size --}}
        <div class="dsp-row dsp-row-col">
          <div class="dsp-row-left">
            <div class="dsp-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/>
              </svg>
            </div>
            <div>
              <div class="dsp-label">Font size</div>
              <div class="dsp-sub">Adjust text size across the application.</div>
            </div>
          </div>
          <div class="dsp-font-pills">
            <button type="button" class="dsp-font-pill" data-size="small" onclick="setFontSize('small')">
              <span style="font-size: 11px; font-weight: 700;">Aa</span> Small
            </button>
            <button type="button" class="dsp-font-pill active" data-size="default" onclick="setFontSize('default')">
              <span style="font-size: 13px; font-weight: 700;">Aa</span> Default
            </button>
            <button type="button" class="dsp-font-pill" data-size="large" onclick="setFontSize('large')">
              <span style="font-size: 15px; font-weight: 700;">Aa</span> Large
            </button>
          </div>
        </div>

        {{-- App Download / Install --}}
        <div class="dsp-row" style="border-top: 1px solid var(--bd, #ece6dd); padding-top: 14px; margin-top: 10px;">
          <div class="dsp-row-left">
            <div class="dsp-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                <line x1="12" y1="18" x2="12.01" y2="18"/>
              </svg>
            </div>
            <div>
              <div class="dsp-label">Install App</div>
              <div class="dsp-sub">Add to Desktop or Home Screen for quick access.</div>
            </div>
          </div>
          <button type="button" class="btn" onclick="installMushFungApp()" style="height: 32px; padding: 0 12px; font-size: 11px; white-space: nowrap;">
            INSTALL
          </button>
        </div>

        {{-- Reset --}}
        <div class="dsp-footer">
          <button type="button" class="dsp-reset-btn" onclick="resetDisplaySettings()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
              <path d="M3 3v5h5"/>
            </svg>
            Reset to default
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Two Column Layout: Personal Info + Change Password --}}
  <div class="profile-grid">
    {{-- Left Card: Personal information --}}
    <div class="profile-card">
      <h2 class="profile-card-title">Personal information</h2>
      
      <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')

        <div class="profile-form-group">
          <label class="profile-label">FULL NAME</label>
          <input type="text" name="name" class="profile-input" value="{{ old('name', $u->name) }}" required>
        </div>

        <div class="profile-form-group">
          <label class="profile-label">CONTACT NUMBER</label>
          <input type="text" name="phone" class="profile-input" value="{{ old('phone', $u->phone) }}" placeholder="+63 900 000 0000">
        </div>

        <div class="profile-form-group">
          <label class="profile-label">USERNAME</label>
          <div class="profile-input-locked-wrap">
            <input type="text" class="profile-input locked" value="{{ $u->username }}" readonly disabled>
            <svg class="profile-lock-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </div>
          <span class="profile-field-hint">Username cannot be changed.</span>
        </div>

        <div class="profile-form-group">
          <label class="profile-label">ROLE</label>
          <div class="profile-input-locked-wrap">
            <input type="text" class="profile-input locked" value="{{ $roleLabel }}" readonly disabled>
            <svg class="profile-lock-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </div>
          <span class="profile-field-hint">Assigned by system administrator.</span>
        </div>

        <button type="submit" class="profile-btn-primary">
          SAVE CHANGES
        </button>
      </form>
    </div>

    {{-- Right Card: Change password --}}
    <div class="profile-card">
      <h2 class="profile-card-title">Change password</h2>

      <form method="POST" action="{{ route('profile.password') }}">
        @csrf
        @method('PUT')

        <div class="profile-form-group">
          <label class="profile-label">CURRENT PASSWORD</label>
          <div class="profile-input-eye-wrap">
            <input type="password" id="curPw" name="current_password" class="profile-input" placeholder="Enter current password" required>
            <button type="button" class="profile-eye-btn" onclick="togglePasswordVisibility('curPw', this)" aria-label="Toggle password visibility">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="7" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="profile-form-group">
          <label class="profile-label">NEW PASSWORD</label>
          <div class="profile-input-eye-wrap">
            <input type="password" id="newPw" name="password" minlength="8" class="profile-input" placeholder="At least 8 characters" required>
            <button type="button" class="profile-eye-btn" onclick="togglePasswordVisibility('newPw', this)" aria-label="Toggle password visibility">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="7" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="profile-form-group">
          <label class="profile-label">CONFIRM NEW PASSWORD</label>
          <div class="profile-input-eye-wrap">
            <input type="password" id="confPw" name="password_confirmation" class="profile-input" placeholder="Re-enter new password" required>
            <button type="button" class="profile-eye-btn" onclick="togglePasswordVisibility('confPw', this)" aria-label="Toggle password visibility">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="7" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="profile-btn-outline">
          UPDATE PASSWORD
        </button>
      </form>
    </div>
  </div>

  {{-- Subtle Account Management options --}}
  <div style="margin-top: 32px; display: flex; justify-content: flex-end; gap: 16px;">
    <button type="button" onclick="document.getElementById('notifDlg').showModal()" style="background:none;border:none;color:#8a7a6e;font-size:12px;font-weight:600;cursor:pointer;text-decoration:underline;">
      Notification preferences
    </button>
    <button type="button" onclick="document.getElementById('deleteAccountDlg').showModal()" style="background:none;border:none;color:#ba2d2d;font-size:12px;font-weight:600;cursor:pointer;text-decoration:underline;">
      Delete account
    </button>
  </div>

</div>

{{-- Notification Preferences Dialog --}}
<dialog id="notifDlg" style="width:min(480px,94vw);border-radius:16px;border:none;padding:24px;box-shadow:0 12px 36px rgba(0,0,0,0.2);background:#ffffff;">
  <form method="POST" action="{{ route('profile.notifications') }}">
    @csrf
    @method('PUT')
    <h3 style="font-size:18px;font-weight:800;color:#241c16;margin-bottom:16px;">Notification preferences</h3>
    <label style="display:flex;align-items:center;gap:10px;margin-bottom:12px;font-size:13px;font-weight:600;cursor:pointer;">
      <input type="checkbox" name="notify_in_app" value="1" @checked($u->notify_in_app) style="accent-color:#43352b;width:16px;height:16px;">
      In-app notifications (bell icon)
    </label>
    <label style="display:flex;align-items:center;gap:10px;margin-bottom:12px;font-size:13px;font-weight:600;cursor:pointer;">
      <input type="checkbox" name="notify_email" value="1" @checked($u->notify_email) style="accent-color:#43352b;width:16px;height:16px;">
      Email notifications for orders and reminders
    </label>
    <label style="display:flex;align-items:center;gap:10px;margin-bottom:12px;font-size:13px;font-weight:600;cursor:pointer;">
      <input type="checkbox" name="notify_sms" value="1" @checked($u->notify_sms) style="accent-color:#43352b;width:16px;height:16px;">
      Text message (SMS) notifications
    </label>
    <p style="font-size:12px;color:#8a7a6e;margin-bottom:20px;line-height:1.4;">
      SMS requires a valid Philippine mobile number saved in your contact number.
    </p>
    <div style="display:flex;justify-content:flex-end;gap:10px;">
      <button type="button" class="btn ghost" onclick="this.closest('dialog').close()" style="background:#f4efe6;border:none;border-radius:8px;padding:8px 16px;font-size:12px;font-weight:700;cursor:pointer;">Cancel</button>
      <button type="submit" style="background:#43352b;color:#fff;border:none;border-radius:8px;padding:8px 18px;font-size:12px;font-weight:700;cursor:pointer;">Save</button>
    </div>
  </form>
</dialog>

{{-- Delete Account Dialog --}}
<dialog id="deleteAccountDlg" style="width:min(440px,94vw);border-radius:16px;border:none;padding:24px;box-shadow:0 12px 36px rgba(0,0,0,0.2);background:#ffffff;">
  <form method="POST" action="{{ route('profile.destroy') }}" data-confirm="Delete your account permanently? This cannot be undone." data-title="Delete account" data-yes="DELETE" data-tone="danger">
    @csrf
    @method('DELETE')
    <h3 style="font-size:18px;font-weight:800;color:#b91c1c;margin-bottom:12px;">Delete account</h3>
    <p style="font-size:13px;color:#55483d;margin-bottom:16px;line-height:1.4;">
      This action cannot be undone. All your data will be permanently deleted.
    </p>
    <div class="profile-form-group">
      <label class="profile-label">CONFIRM YOUR PASSWORD</label>
      <input type="password" name="password" class="profile-input" placeholder="Enter password" required>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
      <button type="button" onclick="this.closest('dialog').close()" style="background:#f4efe6;border:none;border-radius:8px;padding:8px 16px;font-size:12px;font-weight:700;cursor:pointer;">Cancel</button>
      <button type="submit" style="background:#b91c1c;color:#fff;border:none;border-radius:8px;padding:8px 18px;font-size:12px;font-weight:700;cursor:pointer;">Delete Permanently</button>
    </div>
  </form>
</dialog>
@endsection
