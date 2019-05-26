<?php

namespace Hyn\SphinxScout;

use Foolz\SphinxQL\Drivers\Pdo\Connection;
use Foolz\SphinxQL\SphinxQL;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine as AbstractEngine;

class Engine extends AbstractEngine
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $hosts = [];
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $connections;

    public function __construct($hosts = [], array $options = [])
    {
        $this->hosts = collect($hosts);

        $this->connections = $this->hosts->map(function ($host) use ($options) {
            $connection = new Connection();

            $connection->setParam('host', $host);
            $connection->setParam('port', (int)Arr::get($options, 'port', 9306));

            if (Arr::has($options, 'charset')) {
                $connection->setParam('charset', Arr::get($options, 'charset'));
            }

            if (Arr::has($options, 'socket')) {
                $connection->setParam('socket', Arr::get($options, 'socket'));
            }
            return $connection;
        });
    }

    /**
     * @return array
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $models
     *
     * @return void
     */
    public function update($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $example = $models->first();
        $index = $example->searchableAs();
        $columns = array_keys($example->toSearchableArray());

        $this->connections->each(function ($connection) use ($models, $index, $columns) {
            $sphinxQuery = SphinxQL::create($connection)
                ->replace()
                ->into($index)
                ->columns($columns);

            $models->each(function ($model) use (&$sphinxQuery) {
                $sphinxQuery->values($model->toSearchableArray());
            });

            $sphinxQuery->execute();
        });
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $models
     *
     * @return void
     */
    public function delete($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $example = $models->first();
        $index = $example->searchableAs();
        $key = $example->getKey();

        $this->connections->each(function ($connection) use ($models, $index, $key) {
            SphinxQL::create($connection)
                ->delete()
                ->from($index)
                ->where('id', 'IN', $key);
        });
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder $builder
     *
     * @return mixed
     */
    public function search(Builder $builder)
    {
        $example = $builder->model;
        $index = $example->searchableAs();
        $columns = array_keys($example->toSearchableArray());

        return SphinxQL::create($this->connections->random())
            ->from($index)
            ->match($columns, $builder->query)
            ->execute();
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder $builder
     * @param  int                    $perPage
     * @param  int                    $page
     *
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        $example = $builder->model;
        $index = $example->searchableAs();
        $columns = array_keys($example->toSearchableArray());

        return SphinxQL::create($this->connections->random())
            ->from($index)
            ->match($columns, $builder->query)
            ->limit($perPage * ($page - 1), $perPage)
            ->execute();
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  mixed                               $results
     * @param  \Illuminate\Database\Eloquent\Model $model
     *
     * @return Collection
     */
    public function map($results, $model)
    {
        // TODO: Implement map() method.
    }
}