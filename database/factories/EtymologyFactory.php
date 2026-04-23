<?php

namespace Database\Factories;

use App\Models\Etymology;
use App\Models\Word;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Etymology>
 */
class EtymologyFactory extends Factory
{
    protected $model = Etymology::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'word_id' => Word::factory(),
            'parent_word_id' => Word::factory(),
            'relation_type' => $this->faker->randomElement(['derived_from', 'borrowed_from', 'cognate_of', 'compound_of', 'inherited_from']),
            'language_origin' => $this->faker->randomElement(['lat', 'grc', 'gem', 'fra']),
            'notes' => $this->faker->sentence(),
            'source' => 'wiktionary',
        ];
    }
}
