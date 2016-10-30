<?php namespace RainLab\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UsersAddSurname extends Migration
{
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->string('surname')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('surname');
        });
    }
}
