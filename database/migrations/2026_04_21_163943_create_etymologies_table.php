<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etymologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_id')->constrained()->cascadeOnDelete()->comment('The derived/child word');
            $table->foreignId('parent_word_id')->constrained('words')->cascadeOnDelete()->comment('The ancestor/root word');
            $table->string('relation_type', 50)->comment('Type: derived_from, borrowed_from, cognate_of, compound_of, inherited_from');
            $table->string('language_origin', 50)->nullable()->comment('Language the word was borrowed/derived from');
            $table->text('notes')->nullable();
            $table->string('source')->nullable()->comment('Data source: wiktionary, babelnet');
            $table->timestamps();

            $table->index(['word_id', 'relation_type']);
            $table->index('parent_word_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etymologies');
    }
};
