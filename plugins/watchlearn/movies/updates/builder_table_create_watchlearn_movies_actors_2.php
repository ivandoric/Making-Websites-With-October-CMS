<?php namespace Watchlearn\Movies\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateWatchlearnMoviesActors2 extends Migration
{
    public function up()
    {
        Schema::create('watchlearn_movies_actors', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->string('lastname')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('watchlearn_movies_actors');
    }
}
