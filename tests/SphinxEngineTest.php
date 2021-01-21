<?php
namespace Constantable\SphinxScout\Tests;

use Constantable\SphinxScout\SphinxEngine;
use Constantable\SphinxScout\Tests\model\SearchableModel;

use Foolz\SphinxQL\Drivers\ResultSet;
use Foolz\SphinxQL\SphinxQL;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;

class SphinxEngineTest extends MockeryTestCase{

    /**
     * @var Model|Searchable
     */
    private $model;

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function setUp()
    {
        parent::setUp();
        $this->model = new SearchableModel(['id' => 1, 'title' => 'Some text']);
    }

    public function test_update_adds_objects_to_index()
    {
        $client = m::mock(SphinxQL::class);

        $client->shouldReceive('replace')->once()->andReturn($thisObject = m::mock(SphinxQL::class));

        $thisObject->shouldReceive('into')->once()
            ->with('table')
            ->andReturn($thisObject = m::mock(SphinxQL::class));

        $thisObject->shouldReceive('columns')->once()
            ->with(array_keys($this->model->toSearchableArray()))
            ->andReturn($thisObject = m::mock(SphinxQL::class));

        $thisObject->shouldReceive('values')->once()
            ->with($this->model->toSearchableArray())
            ->andReturn($thisObject = m::mock(SphinxQL::class));

        $thisObject->shouldReceive('execute')->once();

        $engine = new SphinxEngine($client);
        $engine->update(Collection::make([$this->model]));
    }

    public function test_delete_removes_objects_to_index()
    {
        $client = m::mock(SphinxQL::class);
        $client->shouldReceive('delete')->once()->andReturn($thisObject = m::mock(SphinxQL::class));

        $thisObject->shouldReceive('from')->once()
            ->with('table')
            ->andReturn($thisObject = m::mock(SphinxQL::class));

        $thisObject->shouldReceive('where')->once()
            ->with('id', '=', $this->model->getKey())
            ->andReturn($thisObject = m::mock(SphinxQL::class));

        $thisObject->shouldReceive('execute')->once();

        $engine = new SphinxEngine($client);
        $engine->delete(Collection::make([$this->model]));
    }

    public function test_search_sends_correct_parameters_to_sphinx()
    {
        $qry = "search query";
        $client = m::mock(SphinxQL::class);
        $client->shouldReceive('select')->once()->with('*')
            ->andReturn($thisObject = m::mock(SphinxQL::class));

        $thisObject->shouldReceive('from')->once()
            ->with('table')
            ->andReturn($thisObject = m::mock(SphinxQL::class));

        $expression = SphinxQL::expr('"' . $qry . '"/1');
        $thisObject->shouldReceive('match')->once()
            ->with(array_keys($this->model->toSearchableArray()), m::on(function ($arg) use ($expression) {
                return $expression == $arg;
            }))
            ->andReturn($thisObject = m::mock(SphinxQL::class));

        $thisObject->shouldReceive('execute')->once();

        $thisObject->shouldReceive('where')->once()
            ->with('foo', '=', 1)
            ->andReturn($thisObject = m::mock(SphinxQL::class));

        $engine = new SphinxEngine($client);
        $builder = new Builder($this->model, $qry);
        $builder->where('foo', 1);
        $engine->search($builder);
    }

    public function test_map_correctly_maps_results_to_models()
    {
        $client = m::mock(SphinxQL::class);
        $engine = new SphinxEngine($client);
        $model = m::mock(stdClass::class);
        $model->shouldReceive('getScoutModelsByIds')->once()->andReturn($models = Collection::make([
            $this->model
        ]));
        $builder = m::mock(Builder::class);
        $resultSet = m::mock(ResultSet::class);
        $resultSet->shouldReceive('fetchAllAssoc')->andReturn($arr = [
            ['id' => 1, 'title' => 'Some text'],
        ]);
        $resultSet->shouldReceive('count')->andReturn($count = 1);
        $results = $engine->map($builder, $resultSet, $model);
        $this->assertEquals(1, count($results));
    }

    public function test_map_method_respects_order()
    {
        $client = m::mock(SphinxQL::class);
        $engine = new SphinxEngine($client);
        $model = m::mock(stdClass::class);
        $model->shouldReceive('getScoutModelsByIds')->andReturn($models = Collection::make([
            new SearchableModel(['id' => 1, 'title' => 'Some text']),
            new SearchableModel(['id' => 2, 'title' => 'Some text 2']),
            new SearchableModel(['id' => 3, 'title' => 'Some text 3']),
            new SearchableModel(['id' => 4, 'title' => 'Some text 4']),
        ]));
        $model->shouldReceive('newQuery');
        $builder = m::mock(Builder::class);

        $resultSet = m::mock(ResultSet::class);
        $resultSet->shouldReceive('fetchAllAssoc')->andReturn($arr = [
            ['id' => 1, 'title' => 'Some text'],
            ['id' => 2, 'title' => 'Some text 2'],
            ['id' => 3, 'title' => 'Some text 3'],
            ['id' => 4, 'title' => 'Some text 4']
        ]);
        $resultSet->shouldReceive('count')->andReturn($count = 4);
        $results = $engine->map($builder, $resultSet, $model);
        $this->assertEquals(4, count($results));
        // It's important we assert with array keys to ensure
        // they have been reset after sorting.
        $this->assertEquals([
            0 => ['id' => 1, 'title' => 'Some text'],
            1 => ['id' => 2, 'title' => 'Some text 2'],
            2 => ['id' => 3, 'title' => 'Some text 3'],
            3 => ['id' => 4, 'title' => 'Some text 4']
        ], $results->toArray());
    }

    public function test_update_empty_searchable_array_does_not_add_objects_to_index()
    {
        $client = m::mock(SphinxQL::class);

        $client->shouldNotReceive('replace');
        $engine = new SphinxEngine($client);
        $engine->update(Collection::make([new EmptySearchableModel]));
        $this->assertTrue(true);
    }
}