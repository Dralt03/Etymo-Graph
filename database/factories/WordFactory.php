<?php

namespace Database\Factories;

use App\Models\Word;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Word>
 */
class WordFactory extends Factory
{
    protected $model = Word::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lemma' => $this->faker->unique()->word(),
            'language' => 'eng',
            'pos' => $this->faker->randomElement(['noun', 'verb', 'adj', 'adv']),
            'source' => 'wiktionary',
            'definition' => $this->faker->sentence(),
        ];
    }
}
