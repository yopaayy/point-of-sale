<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            ['key' => 'nama_toko', 'value' => 'Toko Ku'],
            ['key' => 'alamat', 'value' => 'Jl. Kibandang Samaran Ds. Slangit'],
            ['key' => 'telepon', 'value' => '081234779987'],
            ['key' => 'tipe_nota', 'value' => 'kecil'],
            ['key' => 'diskon', 'value' => '5'],
            ['key' => 'path_logo', 'value' => '/img/logo.png'],
            ['key' => 'path_kartu_member', 'value' => '/img/member.png'],
        ];

        DB::table('pengaturan')->insert($settings);
    }
}
