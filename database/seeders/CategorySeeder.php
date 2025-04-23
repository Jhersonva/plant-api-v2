<?php

namespace Database\Seeders;

use App\Enums\CategoryEnum;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach(CategoryEnum::cases() as $category){
            Category::create([
                'name' => $category->value
            ]);
        }
    }
}
