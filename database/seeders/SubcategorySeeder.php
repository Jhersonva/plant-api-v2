<?php

namespace Database\Seeders;

use App\Enums\SubcategoryEnum;
use App\Models\Subcategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach(SubcategoryEnum::cases() as $subcategory){
            Subcategory::create([
                'name' => $subcategory->value,
                'category_id' => $subcategory->category()
            ]);
        }
    }
}
