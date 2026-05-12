<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Электроника', 'description' => 'Гаджеты и техника'],
            ['name' => 'Книги', 'description' => 'Художественная и техническая литература'],
            ['name' => 'Дом и сад', 'description' => 'Товары для дома и огорода'],
            ['name' => 'Спорт', 'description' => 'Инвентарь и одежда для спорта'],
            ['name' => 'Игрушки', 'description' => 'Детские игрушки и развлечения'],
            ['name' => 'Продукты', 'description' => 'Продукты питания'],
            ['name' => 'Одежда', 'description' => 'Одежда и аксессуары'],
            ['name' => 'Авто', 'description' => 'Автотовары и запчасти'],
        ];

        foreach ($categories as $row) {
            Category::query()->create($row);
        }
    }
}
