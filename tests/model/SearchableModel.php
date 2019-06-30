<?php

namespace Constantable\SphinxScout\Tests\model;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class SearchableModel extends Model
{
    use Searchable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'title'];

    public $isRT = true;

    public function searchableAs()
    {
        return 'table';
    }

    public function scoutMetadata()
    {
        return [];
    }

   /* public function toSearchableArray()
    {
        return ['id'=>1, 'title'=>'Some text'];
    }*/
}