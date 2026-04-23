<?php

namespace Database\Factories;

use App\Models\Synset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Synset>
 */
class SynsetFactory extends Factory
{
    protected $model = Synset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => 'bn:' . $this->faker->unique()->numerify('########') . 'n',
            'pos' => $this->faker->randomElement(['noun', 'verb', 'adj', 'adv']),
            'gloss' => $this->faker->sentence(),
            'source' => 'babelnet',
        ];
    }
}
