<?php namespace Watchlearn\Movies\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateWatchlearnMoviesActorsMovies extends Migration
{
    public function up()
    {
        Schema::create('watchlearn_movies_actors_movies', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('movie_id')->unsigned();
            $table->integer('actor_id')->unsigned();
            $table->primary(['movie_id','actor_id']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('watchlearn_movies_actors_movies');
    }
}
