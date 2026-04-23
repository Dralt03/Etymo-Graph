<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Word extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lemma',
        'language',
        'pos',
        'source',
        'definition',
    ];

    public function synsets(): BelongsToMany
    {
        return $this->belongsToMany(Synset::class, 'word_synsets')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function etymologies(): HasMany
    {
        return $this->hasMany(Etymology::class, 'word_id');
    }

    public function descendants(): HasMany
    {
        return $this->hasMany(Etymology::class, 'parent_word_id');
    }

    /**
     * Scope a query to search by lemma (prefix match).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Word>  $query
     */
    public function scopeSearch(\Illuminate\Database\Eloquent\Builder $query, string $term): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('lemma', 'like', $term.'%');
    }
}
