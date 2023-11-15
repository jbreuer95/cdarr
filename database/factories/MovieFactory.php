<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'radarr_movie_id' => fake()->unique()->randomNumber(6),
            'radarr_file_id' => fake()->unique()->randomNumber(6),
            'path' => '/data/media/movies/' . fake()->name(). '.mp4',
            'title' => fake()->name(),
            'year' => fake()->year(),
            'studio' => fake()->randomElement([
                'Universal Pictures',
                'Paramount Pictures',
                'Warner Bros. Pictures',
                'Walt Disney Pictures',
                'Columbia Pictures',
            ]),
            'quality' => fake()->randomElement([
                '480',
                '720',
                '1080',
            ]),
            'status' => fake()->randomElement([
                'unknown',
                'queued',
                'inspecting',
                'non-compliant',
                'compliant',
                'transcoded',
            ]),
        ];
    }
}
