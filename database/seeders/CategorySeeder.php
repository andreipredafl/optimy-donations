<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Education',
                'slug' => 'education',
                'description' => 'Supporting educational initiatives, scholarships, and learning programs',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Environment',
                'slug' => 'environment',
                'description' => 'Environmental conservation, sustainability, and green initiatives',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Health',
                'slug' => 'health',
                'description' => 'Healthcare support, medical research, and wellness programs',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Community',
                'slug' => 'community',
                'description' => 'Community development, social programs, and local initiatives',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Emergency',
                'slug' => 'emergency',
                'description' => 'Emergency relief, disaster response, and urgent humanitarian aid',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $insertData = [];
        foreach ($categories as $categoryData) {
            if (! Category::where('slug', $categoryData['slug'])->exists()) {
                $insertData[] = $categoryData;
            }
        }
        if (! empty($insertData)) {
            Category::insert($insertData);
        }
    }
}
