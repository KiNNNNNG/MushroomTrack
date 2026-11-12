<?php

use App\Models\BagLoss;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

function userWithRole(string $role): User
{
    return User::factory()->create(['role' => $role, 'status' => 'active']);
}

function makeBatch(string $status, string $code = 'BATCH-900'): Batch
{
    return Batch::create(['code' => $code, 'name' => 'Export test', 'bags' => 5, 'lost_bags' => 0, 'yield_kg' => 0, 'status' => $status]);
}

/* ---------------- Exports ---------------- */

it('lets only admins download exports', function () {
    $this->actingAs(userWithRole('worker'))->get(route('admin.reports.export', ['format' => 'pdf']))->assertForbidden();
    $this->actingAs(userWithRole('customer'))->get(route('admin.store.export', ['format' => 'xlsx']))->assertForbidden();
});

it('rejects an unknown export format', function () {
    $this->actingAs(userWithRole('admin'))->get(route('admin.reports.export', ['format' => 'exe']))->assertSessionHasErrors('format');
});

it('downloads the reports as PDF and Excel', function () {
    $batch = makeBatch('fruiting');
    BagLoss::create(['batch_id' => $batch->id, 'quantity' => 2, 'reason' => 'contamination']);
    $admin = userWithRole('admin');

    $pdf = $this->actingAs($admin)->get(route('admin.reports.export', ['format' => 'pdf']));
    $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
    expect($pdf->getContent())->toStartWith('%PDF');

    $this->actingAs($admin)->get(route('admin.reports.export', ['format' => 'xlsx']))->assertDownload();
});

it('downloads the sales as PDF and Excel', function () {
    $product = Product::create(['name' => 'White Oyster', 'price' => 200, 'stock_kg' => 10]);
    Sale::create(['product_id' => $product->id, 'quantity_kg' => 2, 'amount' => 400, 'sold_at' => now()]);
    $admin = userWithRole('admin');

    $pdf = $this->actingAs($admin)->get(route('admin.store.export', ['format' => 'pdf', 'range' => 'all']));
    $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
    expect($pdf->getContent())->toStartWith('%PDF');

    $this->actingAs($admin)->get(route('admin.store.export', ['format' => 'xlsx', 'range' => 'all']))->assertDownload();
});
