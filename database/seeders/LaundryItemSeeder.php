<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaundryItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('laundry_items')->insert([
            [
                'name' => 'Cuci Kering Standar',
                'category' => 'Standar',
                'price' => 6000,
                'unit' => 'kg',
                'description' => 'Layanan cuci bersih dan dikeringkan wangi, siap pakai (estimasi 2-3 hari).',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuci Komplit (Cuci + Setrika)',
                'category' => 'Komplit',
                'price' => 9000,
                'unit' => 'kg',
                'description' => 'Layanan lengkap cuci bersih, dikeringkan, dan disetrika rapi dengan pewangi premium.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuci Cepat (1 Hari Selesai)',
                'category' => 'Cepat',
                'price' => 12000,
                'unit' => 'kg',
                'description' => 'Layanan cuci dan setrika cepat selesai dalam waktu 24 jam.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuci Kilat Express (Hitungan Jam)',
                'category' => 'Kilat',
                'price' => 18000,
                'unit' => 'kg',
                'description' => 'Layanan prioritas kilat cuci setrika langsung jadi dalam hitungan beberapa jam.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuci Bed Cover Besar',
                'category' => 'Komplit',
                'price' => 30000,
                'unit' => 'pcs',
                'description' => 'Cuci khusus bed cover ukuran king/queen, higienis, bersih dan bebas tungau.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuci Sepatu Premium',
                'category' => 'Kilat',
                'price' => 35000,
                'unit' => 'pasang',
                'description' => 'Deep cleaning sepatu sneakers/casual dengan cairan khusus tanpa merusak bahan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
