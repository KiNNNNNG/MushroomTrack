<?php

use App\Models\BagLoss;
use App\Models\Batch;
use App\Models\Harvest;
use App\Models\MonitoringLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;

function worker(): User
{
    return User::factory()->create(['role' => 'worker', 'status' => 'active']);
}

function fruitingBatch(int $bags = 10): Batch
{
    return Batch::create(['code' => 'T-'.random_int(100, 999), 'name' => 'Test', 'bags' => $bags, 'lost_bags' => 0, 'yield_kg' => 0, 'status' => 'fruiting']);
}

it('adds the harvested kilos to the chosen product stock', function () {
    $batch = fruitingBatch(10);
    $product = Product::create(['name' => 'White Oyster', 'price' => 200, 'stock_kg' => 5]);

    $this->actingAs(worker())->post(route('worker.harvest.store'), [
        'batch_id' => $batch->id, 'product_id' => $product->id, 'weight_kg' => 2.5, 'bags_cleared' => 3,
    ])->assertRedirect();

    expect((float) $product->fresh()->stock_kg)->toBe(7.5)
        ->and((float) $batch->fresh()->yield_kg)->toBe(2.5)
        ->and($batch->fresh()->bags)->toBe(7)
        ->and(Harvest::first()->product_id)->toBe($product->id);
});

it('does not touch store stock when no product is chosen', function () {
    $batch = fruitingBatch(10);
    $product = Product::create(['name' => 'White Oyster', 'price' => 200, 'stock_kg' => 5]);

    $this->actingAs(worker())->post(route('worker.harvest.store'), [
        'batch_id' => $batch->id, 'product_id' => '', 'weight_kg' => 1, 'bags_cleared' => 1,
    ])->assertRedirect();

    expect((float) $product->fresh()->stock_kg)->toBe(5.0)
        ->and((float) $batch->fresh()->yield_kg)->toBe(1.0);
});

it('records lost bags with a reason during the daily check', function () {
    $batch = fruitingBatch(10);

    $this->actingAs(worker())->post(route('worker.monitoring.store', $batch), [
        'watered' => 1, 'photo' => UploadedFile::fake()->image('bag.jpg'),
        'lost_bags' => 2, 'loss_reason' => 'contamination',
    ])->assertRedirect(route('worker.history'));

    $log = MonitoringLog::first();
    expect($batch->fresh()->bags)->toBe(8)
        ->and($batch->fresh()->lost_bags)->toBe(2)
        ->and($log->result)->toBe('attention')
        ->and(BagLoss::first()->reason)->toBe('contamination')
        ->and(BagLoss::first()->monitoring_log_id)->toBe($log->id);

    @unlink(public_path($log->photo_path));
});

it('requires a reason when bags are lost and rejects more than the healthy bags', function () {
    $batch = fruitingBatch(3);
    $w = worker();

    $this->actingAs($w)->post(route('worker.monitoring.store', $batch), [
        'watered' => 1, 'photo' => UploadedFile::fake()->image('bag.jpg'), 'lost_bags' => 1,
    ])->assertSessionHasErrors('loss_reason');

    $this->actingAs($w)->post(route('worker.monitoring.store', $batch), [
        'watered' => 1, 'photo' => UploadedFile::fake()->image('bag.jpg'), 'lost_bags' => 9, 'loss_reason' => 'pests',
    ])->assertSessionHasErrors();

    expect($batch->fresh()->bags)->toBe(3)->and(BagLoss::count())->toBe(0);
});
