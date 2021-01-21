<?php
namespace Constantable\SphinxScout\Tests;

use Constantable\SphinxScout\Tests\model\SearchableModel;

class EmptySearchableModel extends SearchableModel{

	public function toSearchableArray(): array{
		return [];
	}

}