<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('synsets', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique()->comment('BabelNet/WordNet synset ID e.g. bn:00012345n');
            $table->string('pos', 10)->nullable()->comment('Part of speech for this synset');
            $table->text('gloss')->nullable()->comment('Human-readable definition of the synset');
            $table->string('source')->nullable()->comment('Data source: babelnet, wordnet');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('synsets');
    }
};
