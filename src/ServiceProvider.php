<?php
namespace Constantable\SphinxScout;

use Foolz\SphinxQL\Drivers\Pdo\Connection;
use Foolz\SphinxQL\SphinxQL;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider as Provider;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Builder;

class ServiceProvider extends Provider{

    public function boot(){
    	App::make(EngineManager::class)->extend('sphinxsearch',static function($app){
            $options = Config::get('scout.sphinxsearch');
            if (empty($options['socket']))
                unset($options['socket']);
            $connection = new Connection();
            $connection->setParams($options);

            return new SphinxEngine(new SphinxQL($connection));
        });
        Builder::macro('whereIn',static function(string $attribute,array $arrayIn){
            $this->engine()->addWhereIn($attribute, $arrayIn);
            return $this;
        });
    }

}