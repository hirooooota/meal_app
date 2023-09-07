<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            [
                'name' => '野菜',
                'created_at' => date('Y-m-d H-i-s'),
                'updated_at' => date('Y-m-d H-i-s'),
            ],
            [
                'name' => 'タンパク質',
                'created_at' => date('Y-m-d H-i-s'),
                'updated_at' => date('Y-m-d H-i-s'),
            ],
            [
                'name' => '炭水化物',
                'created_at' => date('Y-m-d H-i-s'),
                'updated_at' => date('Y-m-d H-i-s'),
            ]
        ];
        DB::table('categories')->insert($param);
    }
}
