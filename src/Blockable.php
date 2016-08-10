<?php

namespace Racashmoney\Blockable;

/**
 * Copyright (C) 2014 Robert Conner
 */
trait Blockable
{
	/**
	 * Boot the soft taggable trait for a model.
	 *
	 * @return void
	 */
	public static function bootBlockable()
	{
		if(static::removeBlocksOnDelete()) {
			static::deleting(function($model) {
				$model->removeBlocks();
			});
		}
	}
	
	/**
	 * Fetch records that are blocked by a given user.
	 * Ex: Book::whereBlockedBy(123)->get();
	 */
	public function scopeWhereBlockedBy($query, $userId=null)
	{
		if(is_null($userId)) {
			$userId = $this->currLoggedInUserId();
		}
		
		return $query->whereHas('blocks', function($q) use($userId) {
			$q->where('user_id', '=', $userId);
		});
	}
	
	
	/**
	 * Populate the $model->blocks attribute
	 */
	public function getBlockCountAttribute()
	{
		return $this->blockCounter ? $this->blockCounter->count : 0;
	}
	
	/**
	 * Collection of the blocks on this record
	 */
	public function blocks()
	{
		return $this->morphMany(Block::class, 'blockable');
	}

	/**
	 * Counter is a record that stores the total blocks for the
	 * morphed record
	 */
	public function blockCounter()
	{
		return $this->morphOne(BlockCounter::class, 'blockable');
	}
	
	/**
	 * Add a block for this record by the given user.
	 * @param $userId mixed - If null will use currently logged in user.
	 */
	public function block($userId=null)
	{
		if(is_null($userId)) {
			$userId = $this->currLoggedInUserId();
		}
		
		if($userId) {
			$block = $this->blocks()
				->where('user_id', '=', $userId)
				->first();
	
			if($block) return;
	
			$block = new Block();
			$block->user_id = $userId;
			$this->blocks()->save($block);
		}

		$this->incrementBlockCount();
	}

	/**
	 * Remove a block from this record for the given user.
	 * @param $userId mixed - If null will use currently logged in user.
	 */
	public function unblock($userId=null)
	{
		if(is_null($userId)) {
			$userId = $this->currLoggedInUserId();
		}
		
		if($userId) {
			$block = $this->blocks()
				->where('user_id', '=', $userId)
				->first();
	
			if(!$block) { return; }
	
			$block->delete();
		}

		$this->decrementBlockCount();
	}
	
	/**
	 * Has the currently logged in user already "blocked" the current object
	 *
	 * @param string $userId
	 * @return boolean
	 */
	public function blocked($userId=null)
	{
		if(is_null($userId)) {
			$userId = $this->currLoggedInUserId();
		}
		
		return (bool) $this->blocks()
			->where('user_id', '=', $userId)
			->count();
	}
	
	/**
	 * Private. Increment the total block count stored in the counter
	 */
	private function incrementBlockCount()
	{
		$counter = $this->blockCounter()->first();
		
		if($counter) {
			$counter->count++;
			$counter->save();
		} else {
			$counter = new BlockCounter;
			$counter->count = 1;
			$this->blockCounter()->save($counter);
		}
	}
	
	/**
	 * Private. Decrement the total block count stored in the counter
	 */
	private function decrementBlockCount()
	{
		$counter = $this->blockCounter()->first();

		if($counter) {
			$counter->count--;
			if($counter->count) {
				$counter->save();
			} else {
				$counter->delete();
			}
		}
	}
	
	/**
	 * Fetch the primary ID of the currently logged in user
	 * @return number
	 */
	public function currLoggedInUserId()
	{
		return auth()->id();
	}
	
	/**
	 * Did the currently logged in user block this model
	 * Example : if($book->blocked) { }
	 * @return boolean
	 */
	public function getBlockedAttribute()
	{
		return $this->blocked();
	}
	
	/**
	 * Should remove blocks on model row delete (defaults to true)
	 * public static removeBlocksOnDelete = false;
	 */
	public static function removeBlocksOnDelete()
	{
		return isset(static::$removeBlocksOnDelete)
			? static::$removeBlocksOnDelete
			: true;
	}
	
	/**
	 * Delete blocks related to the current record
	 */
	public function removeBlocks()
	{
		Block::where('blockable_type', $this->morphClass)->where('blockable_id', $this->id)->delete();
		
		BlockCounter::where('blockable_type', $this->morphClass)->where('blockable_id', $this->id)->delete();
	}
}
