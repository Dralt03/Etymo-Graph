<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->string('lemma')->index();
            $table->string('language', 10)->default('eng')->index();
            $table->string('pos', 10)->nullable()->comment('Part of speech: noun, verb, adj, adv, etc.');
            $table->string('source')->nullable()->comment('Data source: wiktionary, babelnet');
            $table->text('definition')->nullable();
            $table->timestamps();

            $table->unique(['lemma', 'language', 'pos']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('words');
    }
};
