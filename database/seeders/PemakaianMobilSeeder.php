<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PemakaianMobil;
use Carbon\Carbon;

class PemakaianMobilSeeder extends Seeder
{
    public function run()
    {
        $entries = [
            [
                'mobil_id' => 4,
                'user_id' => 1,
                'tujuan' => 'Seeder: pemakaian mobil id 4',
            ],
            [
                'mobil_id' => 5,
                'user_id' => 2,
                'tujuan' => 'Seeder: pemakaian mobil id 5',
            ],
            [
                'mobil_id' => 6,
                'user_id' => 3,
                'tujuan' => 'Seeder: pemakaian mobil id 6',
            ],
            [
                'mobil_id' => 7,
                'user_id' => 4,
                'tujuan' => 'Seeder: pemakaian mobil id 7',
            ],
        ];

        foreach ($entries as $e) {
            $exists = PemakaianMobil::where('mobil_id', $e['mobil_id'])->where('tujuan', $e['tujuan'])->exists();
            if ($exists) continue;

            PemakaianMobil::create([
                'mobil_id' => $e['mobil_id'],
                'user_id' => $e['user_id'],
                'tanggal_mulai' => Carbon::now()->toDateString(),
                'tanggal_selesai' => Carbon::now()->addDay()->toDateString(),
                'tujuan' => $e['tujuan'],
                'jarak_tempuh_km' => 10,
                'bahan_bakar_liter' => 1.5,
                'catatan' => 'Data dibuat oleh seeder untuk pengujian',
                'status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
