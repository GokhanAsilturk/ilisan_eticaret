<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        return [
            'category_id' => Category::query()->inRandomOrder()->value('id') ?? Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'short_description' => $this->faker->sentence(8),
            'description' => $this->faker->paragraph(),
            'sku' => strtoupper(Str::random(8)),
            'is_active' => true,
            'is_featured' => $this->faker->boolean(15),
            'meta_title' => $this->faker->sentence(5),
            'meta_description' => $this->faker->sentence(12),
        ];
    }
}
