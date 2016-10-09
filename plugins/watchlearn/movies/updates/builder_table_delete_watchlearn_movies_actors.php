<?php namespace Watchlearn\Movies\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteWatchlearnMoviesActors extends Migration
{
    public function up()
    {
        Schema::dropIfExists('watchlearn_movies_actors');
    }
    
    public function down()
    {
        Schema::create('watchlearn_movies_actors', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('lastname')->nullable();
        });
    }
}
