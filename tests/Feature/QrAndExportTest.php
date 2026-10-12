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
    return Batch::create(['code' => $code, 'name' => 'QR test', 'bags' => 5, 'lost_bags' => 0, 'yield_kg' => 0, 'status' => $status]);
}

/* ---------------- QR ---------------- */

it('sends a fruiting batch scan to its daily-check form', function () {
    makeBatch('fruiting');

    $this->actingAs(userWithRole('worker'))->get('/worker/scan/BATCH-900')
        ->assertRedirect(route('worker.monitoring', ['open' => 'BATCH-900']));
});

it('explains why an incubating or harvested batch has no daily check', function () {
    makeBatch('incubating', 'INC-1');
    makeBatch('harvested', 'DONE-1');
    $w = userWithRole('worker');

    $this->actingAs($w)->get('/worker/scan/INC-1')->assertRedirect(route('worker.register'))->assertSessionHasErrors();
    $this->actingAs($w)->get('/worker/scan/DONE-1')->assertRedirect(route('worker.monitoring'))->assertSessionHasErrors();
});

it('asks guests to sign in when they scan a code', function () {
    makeBatch('fruiting');

    $this->get('/worker/scan/BATCH-900')->assertRedirect(route('login'));
});

it('brings workers back to the scanned page after they sign in', function () {
    User::factory()->create(['role' => 'worker', 'status' => 'active', 'username' => 'wally', 'password' => 'secret-pass']);

    $this->withSession(['url.intended' => url('/worker/scan/BATCH-900')])
        ->post('/login', ['username' => 'wally', 'password' => 'secret-pass'])
        ->assertRedirect(url('/worker/scan/BATCH-900'));
});

it('shows printable QR labels to admins only', function () {
    makeBatch('fruiting');

    $this->actingAs(userWithRole('worker'))->get(route('admin.batches.labels'))->assertForbidden();
    $this->actingAs(userWithRole('admin'))->get(route('admin.batches.labels'))
        ->assertOk()->assertSee('BATCH-900')->assertSee('<svg', false);
});

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
