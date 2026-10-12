<?php

namespace Database\Seeders;

use App\Models\Notice;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        if (Notice::exists()) return;

        Notice::toAdmins('New order', 'Kamille Ramos ordered 1kg Fresh Organic White Oyster Mushrooms (ORD-4866).', '/admin/store?tab=orders');
        Notice::toAdmins('New order', 'Samantha Cruz ordered 2kg Fresh Organic White Oyster Mushrooms (ORD-2201).', '/admin/store?tab=orders');
    }
}
