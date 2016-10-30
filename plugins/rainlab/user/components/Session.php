<?php namespace RainLab\User\Components;

use Lang;
use Auth;
use Event;
use Flash;
use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use ValidationException;

class Session extends ComponentBase
{
    const ALLOW_ALL = 'all';
    const ALLOW_GUEST = 'guest';
    const ALLOW_USER = 'user';

    public function componentDetails()
    {
        return [
            'name'        => 'rainlab.user::lang.session.session',
            'description' => 'rainlab.user::lang.session.session_desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'security' => [
                'title'       => 'rainlab.user::lang.session.security_title',
                'description' => 'rainlab.user::lang.session.security_desc',
                'type'        => 'dropdown',
                'default'     => 'all',
                'options'     => [
                    'all'   => 'rainlab.user::lang.session.all',
                    'user'  => 'rainlab.user::lang.session.users',
                    'guest' => 'rainlab.user::lang.session.guests'
                ]
            ],
            'redirect' => [
                'title'       => 'rainlab.user::lang.session.redirect_title',
                'description' => 'rainlab.user::lang.session.redirect_desc',
                'type'        => 'dropdown',
                'default'     => ''
            ]
        ];
    }

    public function getRedirectOptions()
    {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Executed when this component is bound to a page or layout.
     */
    public function onRun()
    {
        $redirectUrl = $this->controller->pageUrl($this->property('redirect'));
        $allowedGroup = $this->property('security', self::ALLOW_ALL);
        $isAuthenticated = Auth::check();

        if (!$isAuthenticated && $allowedGroup == self::ALLOW_USER) {
            return Redirect::guest($redirectUrl);
        }
        elseif ($isAuthenticated && $allowedGroup == self::ALLOW_GUEST) {
            return Redirect::guest($redirectUrl);
        }

        $this->page['user'] = $this->user();
    }

    /**
     * Log out the user
     *
     * Usage:
     *   <a data-request="onLogout">Sign out</a>
     *
     * With the optional redirect parameter:
     *   <a data-request="onLogout" data-request-data="redirect: '/good-bye'">Sign out</a>
     *
     */
    public function onLogout()
    {
        $user = Auth::getUser();

        Auth::logout();

        if ($user) {
            Event::fire('rainlab.user.logout', [$user]);
        }

        $url = post('redirect', Request::fullUrl());
        Flash::success(Lang::get('rainlab.user::lang.session.logout'));

        return Redirect::to($url);
    }

    /**
     * Returns the logged in user, if available, and touches
     * the last seen timestamp.
     * @return RainLab\User\Models\User
     */
    public function user()
    {
        if (!$user = Auth::getUser()) {
            return null;
        }

        $user->touchLastSeen();

        return $user;
    }
}
