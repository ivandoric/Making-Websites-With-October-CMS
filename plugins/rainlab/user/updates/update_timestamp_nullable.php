<?php namespace RainLab\User\Updates;

use Schema;
use DbDongle;
use October\Rain\Database\Updates\Migration;

class UpdateTimestampsNullable extends Migration
{

    public function up()
    {
        DbDongle::disableStrictMode();

        DbDongle::convertTimestamps('users');
        DbDongle::convertTimestamps('user_groups');
        DbDongle::convertTimestamps('rainlab_user_mail_blockers');
    }

    public function down()
    {
        // ...
    }

}
