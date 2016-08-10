<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
use Racashmoney\Blockable\Blockable;
use Racashmoney\Blockable\BlockCounter;

class CommonUseTest extends TestCase
{
	public function setUp()
	{
		parent::setUp();
		
		Eloquent::unguard();

		$this->artisan('migrate', [
		    '--database' => 'testbench',
		    '--realpath' => realpath(__DIR__.'/../migrations'),
		]);
	}
	
	protected function getEnvironmentSetUp($app)
	{
	    $app['config']->set('database.default', 'testbench');
	    $app['config']->set('database.connections.testbench', [
	        'driver'   => 'sqlite',
	        'database' => ':memory:',
	        'prefix'   => '',
	    ]);
	    
		\Schema::create('books', function ($table) {
			$table->increments('id');
			$table->string('name');
			$table->timestamps();
		});
	}
	
	public function tearDown()
	{
		\Schema::drop('books');
	}

	public function test_basic_block()
	{
		$stub = Stub::create(['name'=>123]);
		
		$stub->block();
		
		$this->assertEquals(1, $stub->blockCount);
	}
	
	public function test_multiple_blocks()
	{
		$stub = Stub::create(['name'=>123]);
		
		$stub->block(1);
		$stub->block(2);
		$stub->block(3);
		$stub->block(4);
		
		$this->assertEquals(4, $stub->blockCount);
	}
	
	public function test_unblock()
	{
		$stub = Stub::create(['name'=>123]);
		
		$stub->unblock(1);
		
		$this->assertEquals(0, $stub->blockCount);
	}
	
	public function test_where_blocked_by()
	{
		Stub::create(['name'=>'A'])->block(1);
		Stub::create(['name'=>'B'])->block(1);
		Stub::create(['name'=>'C'])->block(1);
		
		$stubs = Stub::whereBlockedBy(1)->get();
		$shouldBeEmpty = Stub::whereBlockedBy(2)->get();
		
		$this->assertEquals(3, $stubs->count());
		$this->assertEmpty($shouldBeEmpty);
	}
	
	public function test_blocks_get_deletes_with_record()
	{
		$stub1 = Stub::create(['name'=>456]);
		$stub2 = Stub::create(['name'=>123]);
		
		$stub1->block(1);
		$stub1->block(7);
		$stub1->block(8);
		$stub2->block(1);
		$stub2->block(2);
		$stub2->block(3);
		$stub2->block(4);
		
		$stub1->delete();
		
		$results = BlockCounter::all();
		$this->assertEquals(1, $results->count());
	}
	
	public function test_rebuild_test()
	{
		$stub1 = Stub::create(['name'=>456]);
		$stub2 = Stub::create(['name'=>123]);
		
		$stub1->block(1);
		$stub1->block(7);
		$stub1->block(8);
		$stub2->block(1);
		$stub2->block(2);
		$stub2->block(3);
		$stub2->block(4);
		
		BlockCounter::truncate();
		
		BlockCounter::rebuild('Stub');
		
		$results = BlockCounter::all();
		$this->assertEquals(2, $results->count());
	}
}

class Stub extends Eloquent
{
	use Blockable;
	
	protected $morphClass = 'Stub';
	
	protected $connection = 'testbench';
	
	public $table = 'books';
}
