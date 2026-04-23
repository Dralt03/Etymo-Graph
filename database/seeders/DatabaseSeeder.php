<?php

namespace Database\Seeders;

use App\Models\Etymology;
use App\Models\Synset;
use App\Models\Word;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Realistic chain for "compute"
        
        $compute = Word::create([
            'lemma' => 'compute',
            'language' => 'eng',
            'pos' => 'verb',
            'source' => 'wiktionary',
            'definition' => 'To reckon or calculate.'
        ]);

        $computare = Word::create([
            'lemma' => 'computare',
            'language' => 'lat',
            'pos' => 'verb',
            'source' => 'wiktionary',
            'definition' => 'To sum up, reckon, compute.'
        ]);

        $com = Word::create([
            'lemma' => 'com-',
            'language' => 'lat',
            'pos' => 'prefix',
            'source' => 'wiktionary',
            'definition' => 'together'
        ]);

        $putare = Word::create([
            'lemma' => 'putare',
            'language' => 'lat',
            'pos' => 'verb',
            'source' => 'wiktionary',
            'definition' => 'to prune, to reckon, to think.'
        ]);

        $computer = Word::create([
            'lemma' => 'computer',
            'language' => 'eng',
            'pos' => 'noun',
            'source' => 'wiktionary',
            'definition' => 'A programmable electronic device that performs mathematical calculations and logical operations.'
        ]);

        $computation = Word::create([
            'lemma' => 'computation',
            'language' => 'eng',
            'pos' => 'noun',
            'source' => 'wiktionary',
            'definition' => 'The act or process of computing.'
        ]);

        Etymology::create(['word_id' => $compute->id, 'parent_word_id' => $computare->id, 'relation_type' => 'borrowed_from', 'language_origin' => 'lat', 'source' => 'wiktionary']);
        Etymology::create(['word_id' => $computare->id, 'parent_word_id' => $com->id, 'relation_type' => 'compound_of', 'language_origin' => 'lat', 'source' => 'wiktionary']);
        Etymology::create(['word_id' => $computare->id, 'parent_word_id' => $putare->id, 'relation_type' => 'compound_of', 'language_origin' => 'lat', 'source' => 'wiktionary']);
        Etymology::create(['word_id' => $computer->id, 'parent_word_id' => $compute->id, 'relation_type' => 'derived_from', 'language_origin' => 'eng', 'source' => 'wiktionary']);
        Etymology::create(['word_id' => $computation->id, 'parent_word_id' => $compute->id, 'relation_type' => 'derived_from', 'language_origin' => 'eng', 'source' => 'wiktionary']);

        $synsetCompute = Synset::create([
            'external_id' => 'bn:00084343v',
            'pos' => 'verb',
            'gloss' => 'Make a mathematical calculation or computation',
            'source' => 'babelnet'
        ]);

        $synsetComputer = Synset::create([
            'external_id' => 'bn:00021665n',
            'pos' => 'noun',
            'gloss' => 'A machine for performing calculations automatically',
            'source' => 'babelnet'
        ]);

        $compute->synsets()->attach($synsetCompute->id, ['is_primary' => true]);
        $computer->synsets()->attach($synsetComputer->id, ['is_primary' => true]);

        // 2. Realistic chain for "philosophy"
        
        $philosophy = Word::create([
            'lemma' => 'philosophy',
            'language' => 'eng',
            'pos' => 'noun',
            'source' => 'wiktionary',
            'definition' => 'The study of general and fundamental problems concerning matters such as existence, knowledge, values, reason, mind, and language.'
        ]);

        $philosophia = Word::create([
            'lemma' => 'philosophia',
            'language' => 'lat',
            'pos' => 'noun',
            'source' => 'wiktionary',
            'definition' => 'Philosophy.'
        ]);

        $philosophiaGrc = Word::create([
            'lemma' => 'φιλοσοφία',
            'language' => 'grc',
            'pos' => 'noun',
            'source' => 'wiktionary',
            'definition' => 'Love of wisdom.'
        ]);

        Etymology::create(['word_id' => $philosophy->id, 'parent_word_id' => $philosophia->id, 'relation_type' => 'borrowed_from', 'language_origin' => 'lat', 'source' => 'wiktionary']);
        Etymology::create(['word_id' => $philosophia->id, 'parent_word_id' => $philosophiaGrc->id, 'relation_type' => 'borrowed_from', 'language_origin' => 'grc', 'source' => 'wiktionary']);

        $synsetPhilosophy = Synset::create([
            'external_id' => 'bn:00062078n',
            'pos' => 'noun',
            'gloss' => 'The rational investigation of questions about existence and knowledge and ethics',
            'source' => 'babelnet'
        ]);

        $philosophy->synsets()->attach($synsetPhilosophy->id, ['is_primary' => true]);

        // 3. Generate some random words to fill out the UI
        $randomWords = Word::factory()->count(50)->create();
        $randomSynsets = Synset::factory()->count(20)->create();

        foreach ($randomWords as $w) {
            if (rand(0, 1)) {
                $w->synsets()->attach($randomSynsets->random()->id, ['is_primary' => true]);
            }
            if (rand(0, 1)) {
                Etymology::factory()->create([
                    'word_id' => $w->id,
                    'parent_word_id' => $randomWords->random()->id,
                ]);
            }
        }
    }
}
