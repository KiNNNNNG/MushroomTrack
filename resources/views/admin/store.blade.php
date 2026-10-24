@extends('layouts.admin')
@section('content')
@php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp

<div class="tabs subtabs-scroll" style="margin-bottom: 24px; border-bottom: 1px solid #e8e2d8; display: flex; gap: 4px; overflow-x: auto; -webkit-overflow-scrolling: touch; white-space: nowrap; width: 100%; box-sizing: border-box; scrollbar-width: none;">
  @foreach (['products' => 'Products', 'orders' => 'Orders', 'sales' => 'Sales', 'expenses' => 'Expenses', 'history' => 'History'] as $k => $l)
    <a href="{{ route('admin.store', ['tab' => $k]) }}"
       style="padding: 12px 18px; font-size: 13px; font-weight: 800; text-decoration: none; display: inline-block; text-transform: uppercase; letter-spacing: 0.6px; border-bottom: 2px solid {{ $tab === $k ? '#a87834' : 'transparent' }}; color: {{ $tab === $k ? '#a87834' : '#6b5a4e' }}; margin-bottom: -1px; transition: color .15s; flex-shrink: 0;">
      {{ $l }}
    </a>
  @endforeach
</div>

@if ($tab === 'products')
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <span style="font-size: 12px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.6px;">STORE CATALOG</span>
    <button class="btn" onclick="document.getElementById('p-new').showModal()" style="background: #205e46; color: #fff; border: none; border-radius: 8px; padding: 10px 18px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
      + NEW PRODUCT
    </button>
  </div>
  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
    @foreach ($products as $p)
      <div style="background: #ffffff; border: 1px solid #e8e2d8; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 18px rgba(40,30,20, 0.03); display: flex; flex-direction: column;">
        <div style="height: 200px; background: #faf8f5 url('{{ asset($p->image_url) }}') center/cover no-repeat; border-bottom: 1px solid #f2ede5;"></div>
        <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
          <h3 style="font-size: 16px; font-weight: 800; color: #241c16; margin: 0 0 6px;">{{ $p->name }}</h3>
          <p style="font-size: 12px; color: #7c726a; margin: 0 0 16px;">Packed &middot; 1 kg per pack</p>
          <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: auto; margin-bottom: 16px;">
            <span style="font-size: 18px; font-weight: 800; color: #a87834;">₱{{ $fmt($p->price) }} / kg</span>
            <span style="font-size: 12px; color: #6b5a4e; font-weight: 600;">{{ $fmt($p->stock_kg) }} kg in stock</span>
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; border-top: 1px solid #f2ede5; padding-top: 14px;">
            <button type="button" onclick="document.getElementById('p{{ $p->id }}').showModal()" style="background: #faf8f5; border: 1px solid #e8e2d8; border-radius: 8px; padding: 8px 12px; font-size: 12px; font-weight: 800; color: #241c16; cursor: pointer;">
              EDIT
            </button>
            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" data-confirm="Delete this product?" data-title="Delete product" data-yes="DELETE" data-no="CANCEL" data-tone="danger" style="display: contents;">
              @csrf @method('DELETE')
              <button type="submit" style="background: #fff; border: 1px solid #fecaca; border-radius: 8px; padding: 8px 12px; font-size: 12px; font-weight: 800; color: #b91c1c; cursor: pointer;">
                DELETE
              </button>
            </form>
          </div>
        </div>
      </div>
      @include('admin.partials.product-form', ['p' => $p])
    @endforeach
  </div>
  @include('admin.partials.product-form', ['p' => null])

@elseif ($tab === 'orders')
  <div class="table-responsive" style="background: #ffffff; border: 1px solid #e8e2d8; border-radius: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; box-sizing: border-box; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
    <table style="width: 100%; min-width: 620px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #faf8f5; border-bottom: 1px solid #e8e2d8;">
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">ORDER</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">CUSTOMER</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">ADDRESS</th>
          <th style="padding: 14px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">QTY</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">ORDER TYPE</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">STATUS</th>
          <th style="padding: 14px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">ACTIONS</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($orders as $o)
          <tr style="border-bottom: 1px solid #f2ede5;">
            <td style="padding: 14px 20px; font-weight: 700; color: #241c16;">#{{ $o->id }}</td>
            <td style="padding: 14px 20px; color: #4a4037; font-weight: 600;">{{ $o->user->name ?? '—' }}</td>
            <td style="padding: 14px 20px; color: #4a4037; max-width: 240px;">
              @if ($o->fulfillment === 'delivery')
                {{ $o->delivery_address ?: '—' }}
              @else
                <span style="color: #8a7f74;">—</span>
              @endif
            </td>
            <td style="padding: 14px 20px; text-align: center; font-weight: 700; color: #241c16;">{{ $fmt($o->quantity_kg) }} kg</td>
            <td style="padding: 14px 20px;">
              @php $isDeliver = $o->fulfillment === 'delivery'; @endphp
              <span style="display: inline-flex; align-items: center; background: {{ $isDeliver ? '#eff6ff' : '#fef9e7' }}; color: {{ $isDeliver ? '#1d4ed8' : '#b45309' }}; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                {{ $isDeliver ? 'DELIVER' : 'PICK UP' }}
              </span>
            </td>
            <td style="padding: 14px 20px;">
              @php
                $statusColors = [
                  'pending'   => ['bg' => '#fef9e7', 'color' => '#d97706'],
                  'confirmed' => ['bg' => '#e6f7f0', 'color' => '#0e6b47'],
                  'completed' => ['bg' => '#e6f7f0', 'color' => '#0e6b47'],
                  'cancelled' => ['bg' => '#fef2f2', 'color' => '#b91c1c'],
                ];
                $sc = $statusColors[$o->status] ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
              @endphp
              <span style="background: {{ $sc['bg'] }}; color: {{ $sc['color'] }}; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                {{ $o->status === 'confirmed' ? ($isDeliver ? 'READY FOR DELIVERY' : 'READY FOR PICKUP') : strtoupper($o->status) }}
              </span>
            </td>
            <td style="padding: 14px 20px; text-align: center; position: relative;">
              <div style="position: relative; display: inline-block;">
                <button type="button" onclick="toggleOrderMenu({{ $o->id }})" style="background: none; border: none; cursor: pointer; color: #6b5a4e; font-size: 18px; padding: 4px 8px; border-radius: 4px; line-height: 1;" title="Actions">
                  &#8942;
                </button>
                <div id="order-menu-{{ $o->id }}" style="display: none; position: absolute; right: 0; top: 100%; background: #ffffff; border: 1px solid #e8e2d8; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.12); min-width: 170px; z-index: 100; padding: 6px 0; text-align: left;">
                  @if ($o->status === 'pending')
                    <form method="POST" action="{{ route('admin.orders.update', $o) }}">
                      @csrf @method('PUT')
                      <input type="hidden" name="status" value="confirmed">
                      <button type="submit" style="width: 100%; background: none; border: none; padding: 10px 16px; text-align: left; font-size: 13px; font-weight: 700; color: #0e6b47; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        &check; Confirm
                      </button>
                    </form>
                  @endif
                  @if ($o->status === 'confirmed')
                    <form method="POST" action="{{ route('admin.orders.update', $o) }}">
                      @csrf @method('PUT')
                      <input type="hidden" name="status" value="completed">
                      <button type="submit" style="width: 100%; background: none; border: none; padding: 10px 16px; text-align: left; font-size: 13px; font-weight: 700; color: #1d4ed8; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        &check; Completed
                      </button>
                    </form>
                  @endif
                  @if (in_array($o->status, ['pending', 'confirmed']))
                    <form method="POST" action="{{ route('admin.orders.update', $o) }}" data-confirm="Cancel this order?" data-title="Cancel order" data-yes="CANCEL ORDER" data-tone="danger">
                      @csrf @method('PUT')
                      <input type="hidden" name="status" value="cancelled">
                      <button type="submit" style="width: 100%; background: none; border: none; padding: 10px 16px; text-align: left; font-size: 13px; font-weight: 700; color: #b91c1c; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        &times; Cancel
                      </button>
                    </form>
                  @endif
                </div>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" style="padding: 40px 20px; text-align: center; color: #6b5a4e;">No active orders.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <script>
    function toggleOrderMenu(id) {
      document.querySelectorAll('[id^="order-menu-"]').forEach(m => {
        if (m.id !== 'order-menu-' + id) m.style.display = 'none';
      });
      var m = document.getElementById('order-menu-' + id);
      m.style.display = (m.style.display === 'none' || m.style.display === '') ? 'block' : 'none';
    }
    document.addEventListener('click', function(e) {
      if (!e.target.closest('[id^="order-menu-"]') && !e.target.closest('button[onclick^="toggleOrderMenu"]')) {
        document.querySelectorAll('[id^="order-menu-"]').forEach(m => m.style.display = 'none');
      }
    });
  </script>

@elseif ($tab === 'sales')
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 10px; flex-wrap: wrap;">
    <form method="GET" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
      <input type="hidden" name="tab" value="sales">
      <select name="month" onchange="this.form.submit()" style="height: 38px; padding: 0 10px; border-radius: 8px; border: 1px solid #e8e2d8; font-size: 13px; font-weight: 700; background: #fff; color: #241c16; cursor: pointer;">
        @foreach(range(1, 12) as $m)
          <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Month &bull; {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
        @endforeach
      </select>
      <select name="year" onchange="this.form.submit()" style="height: 38px; padding: 0 10px; border-radius: 8px; border: 1px solid #e8e2d8; font-size: 13px; font-weight: 700; background: #fff; color: #241c16; cursor: pointer;">
        @foreach(range(now()->year, now()->year - 4) as $y)
          <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year &bull; {{ $y }}</option>
        @endforeach
      </select>
    </form>
    <button onclick="window.print()" style="background: #205e46; color: #fff; border: none; border-radius: 8px; padding: 10px 16px; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; white-space: nowrap;">
      DOWNLOAD PDF
    </button>
  </div>

  <div style="background: #8a6520; border-radius: 14px; padding: 22px 24px; margin-bottom: 24px; box-shadow: 0 4px 18px rgba(40,30,20, 0.05); width: 100%; box-sizing: border-box;">
    <div style="font-size: 11px; font-weight: 800; color: rgba(255,255,255,0.75); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px;">TOTAL SALES</div>
    <div style="font-size: 32px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">₱{{ number_format($totalSales) }}</div>
  </div>

  <div class="table-responsive" style="background: #ffffff; border: 1px solid #e8e2d8; border-radius: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; box-sizing: border-box; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
    <table style="width: 100%; min-width: 580px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #faf8f5; border-bottom: 1px solid #e8e2d8;">
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">DATE</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">ORDER</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">CUSTOMER</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">TYPE</th>
          <th style="padding: 14px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">QTY</th>
          <th style="padding: 14px 20px; text-align: right; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">AMOUNT</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($sales as $s)
          @php $ord = $s->order ?? null; @endphp
          <tr style="border-bottom: 1px solid #f2ede5;">
            <td style="padding: 14px 20px; color: #4a4037;">{{ \Carbon\Carbon::parse($s->sold_at)->format('Y-m-d') }}</td>
            <td style="padding: 14px 20px; font-weight: 700; color: #241c16;">#{{ $s->order_id ?? $s->id }}</td>
            <td style="padding: 14px 20px; color: #4a4037; font-weight: 600;">{{ $ord?->user?->name ?? 'Customer' }}</td>
            <td style="padding: 14px 20px; color: #4a4037;">{{ $ord ? ($ord->fulfillment === 'delivery' ? 'Deliver' : 'Pick up') : 'Pick up' }}</td>
            <td style="padding: 14px 20px; text-align: center; color: #241c16; font-weight: 700;">{{ $s->quantity_kg ? $fmt($s->quantity_kg).' kg' : ($ord ? $fmt($ord->quantity_kg).' kg' : '—') }}</td>
            <td style="padding: 14px 20px; text-align: right; font-weight: 800; color: #241c16;">₱{{ number_format($s->amount) }}</td>
          </tr>
        @empty
          <tr><td colspan="6" style="padding: 40px 20px; text-align: center; color: #6b5a4e;">No sales recorded for this period.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

@elseif ($tab === 'expenses')
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <button onclick="document.getElementById('add-expense-modal').showModal()" style="background: #205e46; color: #fff; border: none; border-radius: 8px; padding: 10px 18px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
      + ADD EXPENSES
    </button>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
      <form method="GET" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <input type="hidden" name="tab" value="expenses">
        <select name="month" onchange="this.form.submit()" style="height: 38px; padding: 0 10px; border-radius: 8px; border: 1px solid #e8e2d8; font-size: 13px; font-weight: 700; background: #fff; color: #241c16; cursor: pointer;">
          @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Month &bull; {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
          @endforeach
        </select>
        <select name="year" onchange="this.form.submit()" style="height: 38px; padding: 0 10px; border-radius: 8px; border: 1px solid #e8e2d8; font-size: 13px; font-weight: 700; background: #fff; color: #241c16; cursor: pointer;">
          @foreach(range(now()->year, now()->year - 4) as $y)
            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year &bull; {{ $y }}</option>
          @endforeach
        </select>
      </form>
      <button onclick="window.print()" style="background: #205e46; color: #fff; border: none; border-radius: 8px; padding: 10px 16px; font-size: 13px; font-weight: 700; cursor: pointer; white-space: nowrap;">
        DOWNLOAD PDF
      </button>
    </div>
  </div>

  <div style="background: #5c2a22; border-radius: 14px; padding: 22px 24px; margin-bottom: 24px; box-shadow: 0 4px 18px rgba(40,30,20, 0.05); width: 100%; box-sizing: border-box;">
    <div style="font-size: 11px; font-weight: 800; color: rgba(255,255,255,0.75); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px;">TOTAL EXPENSES</div>
    <div style="font-size: 32px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">₱{{ number_format($totalExpenses) }}</div>
  </div>

  <div class="table-responsive" style="background: #ffffff; border: 1px solid #e8e2d8; border-radius: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; box-sizing: border-box; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
    <table style="width: 100%; min-width: 580px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #faf8f5; border-bottom: 1px solid #e8e2d8;">
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">DATE</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">DESCRIPTION</th>
          <th style="padding: 14px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">QUANTITY</th>
          <th style="padding: 14px 20px; text-align: right; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">AMOUNT</th>
          <th style="padding: 14px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">ACTION</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($expenses as $exp)
          <tr style="border-bottom: 1px solid #f2ede5;">
            <td style="padding: 14px 20px; color: #4a4037;">{{ $exp->expense_date->format('Y-m-d') }}</td>
            <td style="padding: 14px 20px; color: #4a4037; font-weight: 600;">{{ $exp->description }}</td>
            <td style="padding: 14px 20px; text-align: center; color: #241c16; font-weight: 700;">{{ $exp->quantity }}</td>
            <td style="padding: 14px 20px; text-align: right; font-weight: 800; color: #241c16;">₱{{ number_format($exp->amount) }}</td>
            <td style="padding: 14px 20px; text-align: center;">
              <form method="POST" action="{{ route('admin.expenses.destroy', $exp) }}" data-confirm="Delete this expense?" data-title="Delete expense" data-yes="DELETE" data-tone="danger">
                @csrf @method('DELETE')
                <button type="submit" style="background: none; border: none; color: #b91c1c; font-size: 12px; font-weight: 800; cursor: pointer;">DELETE</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" style="padding: 40px 20px; text-align: center; color: #6b5a4e;">No expenses recorded for this period.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Add Expense Modal --}}
  <dialog id="add-expense-modal" style="border: none; border-radius: 16px; padding: 32px; width: 480px; max-width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
    <form method="POST" action="{{ route('admin.expenses.store') }}">
      @csrf
      <h3 style="font-size: 20px; font-weight: 800; color: #241c16; margin: 0 0 24px;">Add Expense</h3>
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">DATE</label>
        <input type="date" name="expense_date" value="{{ now()->format('Y-m-d') }}" required style="width: 100%; height: 44px; border: 1px solid #e8e2d8; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #241c16; background: #faf8f5; box-sizing: border-box;">
      </div>
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">DESCRIPTION</label>
        <input type="text" name="description" placeholder="e.g. Delivery fuel" required style="width: 100%; height: 44px; border: 1px solid #e8e2d8; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #241c16; background: #faf8f5; box-sizing: border-box;">
      </div>
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">QUANTITY</label>
        <input type="number" name="quantity" value="1" min="1" required style="width: 100%; height: 44px; border: 1px solid #e8e2d8; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #241c16; background: #faf8f5; box-sizing: border-box;">
      </div>
      <div style="margin-bottom: 24px;">
        <label style="display: block; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">AMOUNT (₱)</label>
        <input type="number" name="amount" placeholder="450" step="0.01" min="0" required style="width: 100%; height: 44px; border: 1px solid #e8e2d8; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #241c16; background: #faf8f5; box-sizing: border-box;">
      </div>
      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="document.getElementById('add-expense-modal').close()" style="background: #fff; border: 1px solid #e8e2d8; border-radius: 8px; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer; color: #241c16;">CANCEL</button>
        <button type="submit" style="background: #205e46; color: #fff; border: none; border-radius: 8px; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer;">SUBMIT</button>
      </div>
    </form>
  </dialog>

@else
  {{-- History tab --}}
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
    <p style="font-size: 13px; color: #6b5a4e; margin: 0;">Completed and cancelled orders</p>
    <button type="button" onclick="deleteSelectedHistory()" style="background: #b91c1c; color: #fff; border: none; border-radius: 8px; padding: 10px 16px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
      DELETE
    </button>
  </div>
  <form id="delete-history-form" method="POST" action="{{ route('admin.history.destroy') }}" data-confirm="Delete selected records?" data-title="Delete records" data-yes="DELETE" data-tone="danger">
    @csrf @method('DELETE')
    <div class="table-responsive" style="background: #ffffff; border: 1px solid #e8e2d8; border-radius: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; box-sizing: border-box; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
      <table style="width: 100%; min-width: 620px; border-collapse: collapse; font-size: 14px;">
        <thead>
          <tr style="background: #faf8f5; border-bottom: 1px solid #e8e2d8;">
            <th style="padding: 14px 20px; width: 36px; text-align: center;">
              <input type="checkbox" onclick="toggleAllHistory(this)" style="width: 15px; height: 15px; accent-color: #205e46; cursor: pointer;">
            </th>
            <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">DATE</th>
            <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">ORDER</th>
            <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">CUSTOMER</th>
            <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">ORDER TYPE</th>
            <th style="padding: 14px 20px; text-align: right; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">AMOUNT</th>
            <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">STATUS</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($history as $o)
            <tr style="border-bottom: 1px solid #f2ede5;">
              <td style="padding: 14px 20px; text-align: center;">
                <input type="checkbox" name="ids[]" value="{{ $o->id }}" style="width: 15px; height: 15px; accent-color: #205e46; cursor: pointer;">
              </td>
              <td style="padding: 14px 20px; color: #4a4037;">{{ $o->created_at->format('Y-m-d') }}</td>
              <td style="padding: 14px 20px; font-weight: 700; color: #241c16;">#{{ $o->id }}</td>
              <td style="padding: 14px 20px; color: #4a4037; font-weight: 600;">{{ $o->user->name ?? 'Customer' }}</td>
              <td style="padding: 14px 20px; color: #4a4037;">{{ $o->fulfillment === 'delivery' ? 'Deliver' : 'Pick up' }}</td>
              <td style="padding: 14px 20px; text-align: right; font-weight: 800; color: #241c16;">₱{{ number_format($o->total) }}</td>
              <td style="padding: 14px 20px;">
                @php $isComp = $o->status === 'completed'; @endphp
                <span style="background: {{ $isComp ? '#e6f7f0' : '#fef2f2' }}; color: {{ $isComp ? '#0e6b47' : '#b91c1c' }}; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                  {{ strtoupper($o->status) }}
                </span>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" style="padding: 40px 20px; text-align: center; color: #6b5a4e;">No completed or cancelled orders yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </form>
  <script>
    function toggleAllHistory(master) {
      document.querySelectorAll('#delete-history-form input[name="ids[]"]').forEach(cb => cb.checked = master.checked);
    }
    function deleteSelectedHistory() {
      var checked = document.querySelectorAll('#delete-history-form input[name="ids[]"]:checked');
      if (checked.length === 0) {
        alert('Please select at least one record to delete.');
        return;
      }
      if (confirm('Delete the selected history records?')) {
        document.getElementById('delete-history-form').submit();
      }
    }
  </script>
@endif

@endsection
