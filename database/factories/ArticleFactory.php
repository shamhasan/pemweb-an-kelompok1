<?php

namespace Database\Factories;

use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'category_id' => ArticleCategory::factory(),

            'title' => $this->faker->unique()->sentence(6),
            'content' => collect($this->faker->paragraphs(mt_rand(6, 12)))->join("\n\n"),
            'image_url' => $this->faker->imageUrl(1200, 630),
            'status' => $this->faker->randomElement(['published', 'draft']),
        ];
    }

    // Helper
    public function published()
    {
        return $this->state(fn() => ['status' => 'published']);
    }

    public function draft()
    {
        return $this->state(fn() => ['status' => 'draft']);
    }

    public function forAuthor(User $user)
    {
        return $this->state(fn() => ['author_id' => $user->id]);
    }

    public function forCategory(ArticleCategory $category)
    {
        return $this->state(fn() => ['category_id' => $category->id]);
    }
}
