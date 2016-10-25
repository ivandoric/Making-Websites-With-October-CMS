<?php namespace Watchlearn\Contact\Components;

use Cms\Classes\ComponentBase;
use Input;
use Mail;

class ContactForm extends ComponentBase
{

    public function componentDetails(){
        return [
            'name' => 'Contact Form',
            'description' => 'Simple contact form'
        ];
    }


    public function onSend(){

        $vars = ['name' => Input::get('name'), 'email' => Input::get('email'), 'content' => Input::get('content')];

        Mail::send('watchlearn.contact::mail.message', $vars, function($message) {

            $message->to('youremail@gmail.com', 'Admin Person');
            $message->subject('New message from contact form');

        });

    }

}