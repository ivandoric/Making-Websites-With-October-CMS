<?php namespace Watchlearn\Movies\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateWatchlearnMovies4 extends Migration
{
    public function up()
    {
        Schema::table('watchlearn_movies_', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->boolean('published')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('watchlearn_movies_', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('published');
        });
    }
}
