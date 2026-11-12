<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('renders product availability without a database query for each stock label', function () {
    $user = User::factory()->create(['role' => 'customer', 'status' => 'active']);
    for ($i = 0; $i < 10; $i++) {
        $product = Product::create(['name' => "White Oyster {$i}", 'price' => 200, 'stock_kg' => 10]);
        Order::place($user, $product, 3);
    }
    DB::enableQueryLog();
    DB::flushQueryLog();
    $this->actingAs($user)->get(route('customer.shop'))->assertOk()->assertSee('7 kg');
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();
    expect($queries)->toBeLessThanOrEqual(8);
});

it('keeps asset URLs stable when the clock advances between page visits', function () {
    $first = $this->get(route('login'))->assertOk()->getContent();
    $this->travel(2)->seconds();
    $second = $this->get(route('login'))->assertOk()->getContent();
    preg_match_all('/(?:auth\.css|mushroomtrack\.js)\?v=[a-zA-Z0-9]+/', $first, $firstUrls);
    preg_match_all('/(?:auth\.css|mushroomtrack\.js)\?v=[a-zA-Z0-9]+/', $second, $secondUrls);
    expect($firstUrls[0])->toHaveCount(2)->and($secondUrls[0])->toBe($firstUrls[0]);
});

it('loads the store product tab without querying sales and expenses', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    DB::enableQueryLog();
    DB::flushQueryLog();
    $this->actingAs($admin)->get(route('admin.store'))->assertOk();
    $queries = DB::getQueryLog();
    DB::disableQueryLog();
    expect(count($queries))->toBeLessThanOrEqual(5);
    foreach ($queries as $query) {
        expect($query['query'])->not->toContain('"sales"')->not->toContain('"expenses"');
    }
});
