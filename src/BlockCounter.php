<?php

namespace Racashmoney\Blockable;

use Illuminate\Database\Eloquent\Model as Eloquent;

class BlockCounter extends Eloquent
{
	protected $table = 'blockable_block_counters';
	public $timestamps = false;
	protected $fillable = ['blockable_id', 'blockable_type', 'count'];
	
	public function blockable()
	{
		return $this->morphTo();
	}
	
	/**
	 * Delete all counts of the given model, and recount them and insert new counts
	 *
	 * @param string $model (should match Model::$morphClass)
	 */
	public static function rebuild($modelClass)
	{
		if(empty($modelClass)) {
			throw new \Exception('$modelClass cannot be empty/null. Maybe set the $morphClass variable on your model.');
		}
		
		$builder = Block::query()
			->select(\DB::raw('count(*) as count, blockable_type, blockable_id'))
			->where('blockable_type', $modelClass)
			->groupBy('blockable_id');
		
		$results = $builder->get();
		
		$inserts = $results->toArray();
		
		\DB::table((new static)->table)->insert($inserts);
	}
	
}