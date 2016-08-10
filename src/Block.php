<?php

namespace Racashmoney\Blockable;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Block extends Eloquent
{
	protected $table = 'blockable_blocks';
	public $timestamps = true;
	protected $fillable = ['blockable_id', 'blockable_type', 'user_id'];

	public function blockable()
	{
		return $this->morphTo();
	}
}