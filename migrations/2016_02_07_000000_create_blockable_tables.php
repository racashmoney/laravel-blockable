<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBlockableTables extends Migration
{
	public function up()
	{
		Schema::create('blockable_blocks', function(Blueprint $table) {
			$table->increments('id');
			$table->string('blockable_id', 36);
			$table->string('blockable_type', 255);
			$table->string('user_id', 36)->index();
			$table->timestamps();
			$table->unique(['blockable_id', 'blockable_type', 'user_id'], 'blockable_blocks_unique');
		});
		
		Schema::create('blockable_block_counters', function(Blueprint $table) {
			$table->increments('id');
			$table->string('blockable_id', 36);
			$table->string('blockable_type', 255);
			$table->unsignedInteger('count')->default(0);
			$table->unique(['blockable_id', 'blockable_type'], 'blockable_counts');
		});
		
	}

	public function down()
	{
		Schema::drop('blockable_blocks');
		Schema::drop('blockable_block_counters');
	}
}
