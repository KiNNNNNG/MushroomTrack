<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MushroomTrackSeeder extends Seeder
{
    public function run(): void
    {
        User::unguarded(function () {
            foreach ([
                ['Eleanor Vance',  'eleanor@mushroomtrack.test', 'admin'],
                ['Juan Dela Cruz', 'juan@mushroomtrack.test',    'worker'],
                ['Kamille Ramos',  'kamille@mushroomtrack.test', 'customer'],
            ] as [$name, $email, $role]) {
                User::updateOrCreate(['email' => $email], [
    'name' => $name, 'username' => \Illuminate\Support\Str::before($email, '@'),
    'role' => $role, 'status' => 'active',
    'password' => Hash::make('password'), 'email_verified_at' => now(),
]);
            }
        });

        foreach ([
            ['BATCH-001', 'White Oyster Premium Lot',     115, 5, 50,   'fruiting',   '2023-11-15'],
            ['BATCH-002', 'Grey Mushroom High Yield Lot', 200, 0, 0,    'incubating', '2023-11-20'],
            ['BATCH-003', 'Commercial Spore Cluster',     140, 0, 70.5, 'fruiting',   '2023-10-31'],
        ] as [$code, $name, $bags, $lost, $yield, $status, $date]) {
            Batch::updateOrCreate(['code' => $code], [
                'name' => $name, 'bags' => $bags, 'lost_bags' => $lost,
                'yield_kg' => $yield, 'status' => $status, 'harvest_date' => $date,
            ]);
        }

        $white = Product::updateOrCreate(['name' => 'Fresh Organic White Oyster Mushrooms'], [
            'price' => 180, 'stock_kg' => 45,
            'image_url' => '/images/products/white-oyster-mushrooms.jpg',
        ]);
        Product::updateOrCreate(['name' => 'Premium Grey Oyster Mushrooms Allocation'], [
            'price' => 160, 'stock_kg' => 150,
            'image_url' => '/images/products/grey-oyster-mushrooms.jpg',
        ]);

        if (! Sale::exists()) {
            Sale::create(['product_id' => $white->id, 'quantity_kg' => 5, 'amount' => 900, 'sold_at' => now()]);
        }
    }
}
