<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\MonitoringLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $white = Product::where('name', 'like', 'Fresh Organic White%')->first();
        $grey  = Product::where('name', 'like', 'Premium Grey%')->first();
        $white?->update(['description' => 'Grown with zero chemicals. Picked clean daily.']);
        $grey?->update(['description' => 'Pre-order from the upcoming clean room harvest cycle.']);

        $mk = fn ($name, $email) => User::unguarded(fn () => User::updateOrCreate(['email' => $email], [
    'name' => $name, 'username' => \Illuminate\Support\Str::before($email, '@'), 'role' => 'customer', 'status' => 'active',
    'password' => Hash::make('password'), 'email_verified_at' => now(),
]));
        $derrick  = $mk('Derrick Harrison', 'derrick@mushroomtrack.test');
        $samantha = $mk('Samantha Cruz', 'samantha@mushroomtrack.test');
        $kamille  = User::where('email', 'kamille@mushroomtrack.test')->first();

        if (! $white) return;

        $done = Order::firstOrCreate(['code' => 'ORD-1031'], [
            'user_id' => $derrick->id, 'product_id' => $white->id, 'quantity_kg' => 5, 'total' => 900,
            'status' => 'completed', 'created_at' => '2023-10-31 10:00:00', 'updated_at' => '2023-10-31 10:00:00',
        ]);
        Sale::whereNull('order_id')->first()?->update(['order_id' => $done->id]);

        Order::firstOrCreate(['code' => 'ORD-2201'], [
            'user_id' => $samantha->id, 'product_id' => $white->id, 'quantity_kg' => 2, 'total' => 360, 'status' => 'confirmed',
        ]);

        if ($kamille) {
            Order::firstOrCreate(['code' => 'ORD-4866'], [
                'user_id' => $kamille->id, 'product_id' => $white->id, 'quantity_kg' => 1, 'total' => 180, 'status' => 'pending',
            ]);
            CartItem::firstOrCreate(['user_id' => $kamille->id, 'product_id' => $white->id], ['quantity_kg' => 1]);
        }

        $juan = User::where('email', 'juan@mushroomtrack.test')->first();
        if ($juan && MonitoringLog::count() === 0) {
            $b1 = \App\Models\Batch::where('code', 'BATCH-001')->first();
            $b3 = \App\Models\Batch::where('code', 'BATCH-003')->first();
            if ($b1) MonitoringLog::create(['batch_id' => $b1->id, 'user_id' => $juan->id, 'watered' => true, 'result' => 'healthy',
                'notes' => 'Mushrooms growing cleanly on all substrate bags.', 'photo_path' => $white->image_url, 'created_at' => '2023-10-25 09:00:00']);
            if ($b3) MonitoringLog::create(['batch_id' => $b3->id, 'user_id' => $juan->id, 'watered' => true, 'result' => 'healthy',
                'notes' => 'Caps look fully developed and clean.', 'photo_path' => $white->image_url, 'created_at' => '2023-10-26 09:00:00']);
        }
    }
}
