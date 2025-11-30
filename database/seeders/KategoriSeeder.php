<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('kategori')->insert([
            // Expense categories
            ['name' => 'Pakaian', 'kind' => 'expense', 'icon' => 'checkroom', 'color' => '#FF6F61'],
            ['name' => 'Pendidikan', 'kind' => 'expense', 'icon' => 'school', 'color' => '#4CAF50'],
            ['name' => 'Makanan', 'kind' => 'expense', 'icon' => 'restaurant', 'color' => '#FF9800'],
            ['name' => 'Hadiah', 'kind' => 'expense', 'icon' => 'card_giftcard', 'color' => '#9C27B0'],
            ['name' => 'Angkutan', 'kind' => 'expense', 'icon' => 'local_taxi', 'color' => '#03A9F4'],
            ['name' => 'Perjalanan', 'kind' => 'expense', 'icon' => 'directions_car', 'color' => '#795548'],
            ['name' => 'Belanja', 'kind' => 'expense', 'icon' => 'shopping_bag', 'color' => '#E91E63'],
            ['name' => 'Hiburan', 'kind' => 'expense', 'icon' => 'videogame_asset', 'color' => '#673AB7'],

            // Income categories
            ['name' => 'Gaji', 'kind' => 'income', 'icon' => 'attach_money', 'color' => '#009688'],
            ['name' => 'Freelance', 'kind' => 'income', 'icon' => 'laptop', 'color' => '#2196F3'],
            ['name' => 'Investasi', 'kind' => 'income', 'icon' => 'trending_up', 'color' => '#4CAF50'],
            ['name' => 'Bonus', 'kind' => 'income', 'icon' => 'card_giftcard', 'color' => '#FF5722'],
            ['name' => 'Hadiah', 'kind' => 'income', 'icon' => 'card_giftcard', 'color' => '#9C27B0'],
            ['name' => 'Lainnya', 'kind' => 'income', 'icon' => 'more_horiz', 'color' => '#9E9E9E'],
        ]);
    }
}
