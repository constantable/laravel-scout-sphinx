<?php

namespace Constantable\SphinxScout\Tests\Model;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class SearchableModel extends Model
{

//    use Searchable;

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = ['id', 'title'];

    public $isRT = true;

    public function searchableAs(): string
    {
        return 'table';
    }

    public function scoutMetadata(): array
    {
        return [];
    }

    public function getScoutKey()
    {
        return $this->getKey();
    }

    public function getKey()
    {
        return $this->attributesToArray()["id"];
    }

    public function toSearchableArray()
    {
        //return ['id' => 1, 'title' => 'Some text'];
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }
}
