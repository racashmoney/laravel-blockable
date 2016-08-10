<?php

use Mockery as m;
use Racashmoney\Blockable\Blockable;

class CounterTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}
	
	public function testBlock()
	{
		$blockable = m::mock('BlockableStub[incrementBlockCount]');
		$blockable->shouldReceive('incrementBlockCount')->andReturn(null);
		
		$blockable->block(0);
	}
	
	public function testUnblock()
	{
		$blockable = m::mock('BlockableStub[decrementBlockCount]');
		$blockable->shouldReceive('decrementBlockCount')->andReturn(null);
		
		$blockable->unblock(0);
	}
	
}

class BlockableStub extends \Illuminate\Database\Eloquent\Model
{
	use Blockable;

	public function incrementBlockCount() {}
	public function decrementBlockCount() {}
}