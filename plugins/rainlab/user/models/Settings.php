<?php namespace RainLab\User\Models;

use Lang;
use Model;
use System\Models\MailTemplate;
use RainLab\User\Models\User as UserModel;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'user_settings';
    public $settingsFields = 'fields.yaml';

    const ACTIVATE_AUTO = 'auto';
    const ACTIVATE_USER = 'user';
    const ACTIVATE_ADMIN = 'admin';

    const LOGIN_EMAIL = 'email';
    const LOGIN_USERNAME = 'username';

    public function initSettingsData()
    {
        $this->require_activation = true;
        $this->activate_mode = self::ACTIVATE_AUTO;
        $this->use_throttle = true;
        $this->block_persistence = false;
        $this->allow_registration = true;
        $this->welcome_template = 'rainlab.user::mail.welcome';
        $this->login_attribute = self::LOGIN_EMAIL;
    }

    public function getActivateModeOptions()
    {
        return [
            self::ACTIVATE_AUTO => ['rainlab.user::lang.settings.activate_mode_auto', 'rainlab.user::lang.settings.activate_mode_auto_comment'],
            self::ACTIVATE_USER => ['rainlab.user::lang.settings.activate_mode_user', 'rainlab.user::lang.settings.activate_mode_user_comment'],
            self::ACTIVATE_ADMIN => ['rainlab.user::lang.settings.activate_mode_admin', 'rainlab.user::lang.settings.activate_mode_admin_comment'],
        ];
    }

    public function getLoginAttributeOptions()
    {
        return [
            self::LOGIN_EMAIL => ['rainlab.user::lang.login.attribute_email'],
            self::LOGIN_USERNAME => ['rainlab.user::lang.login.attribute_username'],
        ];
    }

    public function getActivateModeAttribute($value)
    {
        if (!$value) {
            return self::ACTIVATE_AUTO;
        }

        return $value;
    }

    public function getWelcomeTemplateOptions()
    {
        $codes = array_keys(MailTemplate::listAllTemplates());
        $result = [''=>'- '.Lang::get('rainlab.user::lang.settings.no_mail_template').' -'];
        $result += array_combine($codes, $codes);
        return $result;
    }
}