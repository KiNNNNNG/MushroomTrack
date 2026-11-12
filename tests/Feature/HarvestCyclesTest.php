<?php

use App\Models\Batch;
use App\Models\Harvest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->worker = User::factory()->create(['role' => 'worker', 'status' => 'active']);
    $this->product = Product::create(['name' => 'White Oyster', 'price' => 200, 'stock_kg' => 5]);
    $this->batch = Batch::create(['code' => 'FOUR', 'name' => 'White Oyster', 'bags' => 10,
        'status' => 'fruiting', 'harvest_date' => today()]);
});

function cyclePayload(Batch $batch, int $flush = 1): array
{
    return ['batch_id' => $batch->id, 'weight_kg' => 2.5, 'flush_number' => $flush, 'submission_id' => (string) Str::uuid()];
}

it('records four harvests then disposes the bags and refuses a fifth', function () {
    for ($flush = 1; $flush <= 4; $flush++) {
        $this->batch->refresh()->update(['harvest_date' => today()]);
        $this->actingAs($this->worker)->post(route('worker.harvest.store'), cyclePayload($this->batch, $flush))
            ->assertSessionHasNoErrors()->assertRedirect(route($flush === 4 ? 'worker.completed' : 'worker.harvest'));
        $batch = $this->batch->fresh();
        expect($batch->flush_count)->toBe($flush)
            ->and($batch->status)->toBe($flush === 4 ? 'disposed' : 'fruiting')
            ->and($batch->bags)->toBe($flush === 4 ? 0 : 10);
    }
    expect($this->batch->fresh()->disposed_at)->not->toBeNull();
    expect((float) $this->product->fresh()->stock_kg)->toBe(15.0)
        ->and((float) $this->batch->fresh()->yield_kg)->toBe(10.0)
        ->and(Harvest::orderBy('id')->pluck('bags_cleared')->all())->toBe([0, 0, 0, 10]);
    $this->post(route('worker.harvest.store'), cyclePayload($this->batch, 4))->assertSessionHasErrors();
    expect(Harvest::count())->toBe(4);
    $this->get(route('worker.completed'))->assertOk()->assertSee('Disposed');
});

it('does not add stock or notices again when a submitted harvest is retried', function () {
    $payload = cyclePayload($this->batch);
    $this->actingAs($this->worker)->post(route('worker.harvest.store'), $payload)->assertSessionHasNoErrors();
    $this->post(route('worker.harvest.store'), $payload)->assertSessionHasNoErrors();
    expect(Harvest::count())->toBe(1)->and((float) $this->product->fresh()->stock_kg)->toBe(7.5);
    $this->post(route('worker.harvest.store'), cyclePayload($this->batch))->assertSessionHasErrors('flush_number');
    expect(Harvest::count())->toBe(1);
});

it('rejects a reused submission with a changed weight', function () {
    $payload = cyclePayload($this->batch);
    $this->actingAs($this->worker)->post(route('worker.harvest.store'), $payload);
    $payload['weight_kg'] = 3;
    $this->post(route('worker.harvest.store'), $payload)->assertSessionHasErrors('submission_id');
    expect(Harvest::count())->toBe(1);
});

it('requires the correct cycle and an active batch that is due', function () {
    $this->actingAs($this->worker)->post(route('worker.harvest.store'), cyclePayload($this->batch, 2))->assertSessionHasErrors('flush_number');
    $this->batch->update(['harvest_date' => today()->addDay()]);
    $this->post(route('worker.harvest.store'), cyclePayload($this->batch))->assertSessionHasErrors('batch_id');
    $this->batch->update(['status' => 'incubating', 'harvest_date' => today()]);
    $this->post(route('worker.harvest.store'), cyclePayload($this->batch))->assertSessionHasErrors('batch_id');
    expect(Harvest::count())->toBe(0)->and((float) $this->product->fresh()->stock_kg)->toBe(5.0);
});

it('keeps the harvest unchanged when the stock product is missing', function () {
    $this->product->delete();
    $this->actingAs($this->worker)->post(route('worker.harvest.store'), cyclePayload($this->batch))->assertSessionHasErrors('batch_id');
    expect(Harvest::count())->toBe(0)->and($this->batch->fresh()->flush_count)->toBe(0);
});

it('backfills old harvest cycles and reopens batches closed after one harvest', function () {
    Harvest::create(['batch_id' => $this->batch->id, 'user_id' => $this->worker->id,
        'product_id' => $this->product->id, 'weight_kg' => 2, 'bags_cleared' => 10]);
    $this->batch->update(['status' => 'harvested', 'yield_kg' => 2]);
    \Illuminate\Support\Facades\Schema::table('harvests', function ($table) {
        $table->dropUnique(['batch_id', 'flush_number']);
        $table->dropUnique(['submission_id']);
        $table->dropColumn(['submission_id', 'flush_number']);
    });
    $migration = require database_path('migrations/2026_11_12_000001_protect_harvest_cycles.php');
    $migration->up();
    $batch = $this->batch->fresh();
    expect($batch->flush_count)->toBe(1)->and($batch->max_flushes)->toBe(4)
        ->and($batch->status)->toBe('fruiting')->and($batch->bags)->toBe(10)
        ->and((int) Harvest::first()->flush_number)->toBe(1)
        ->and((float) $this->product->fresh()->stock_kg)->toBe(5.0);
});
