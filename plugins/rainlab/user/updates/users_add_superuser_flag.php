<?php namespace RainLab\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UsersAddSuperuserFlag extends Migration
{
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->boolean('is_superuser')->default(false);
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('is_superuser');
        });
    }
}
