<?php namespace OFFLINE\SiteSearch\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineSitesearchQueryLogs extends Migration
{
    public function up()
    {
        Schema::create('offline_sitesearch_query_logs', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->text('query');
            $table->string('location')->nullable();
            $table->string('domain')->nullable();
            $table->string('useragent')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_sitesearch_query_logs');
    }
}
