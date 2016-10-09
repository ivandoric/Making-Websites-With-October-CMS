<?php namespace Watchlearn\Movies\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateWatchlearnMovies3 extends Migration
{
    public function up()
    {
        Schema::table('watchlearn_movies_', function($table)
        {
            $table->dropColumn('actors');
        });
    }
    
    public function down()
    {
        Schema::table('watchlearn_movies_', function($table)
        {
            $table->text('actors')->nullable();
        });
    }
}
