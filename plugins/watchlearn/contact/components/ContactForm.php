<?php namespace Watchlearn\Contact\Components;

use Cms\Classes\ComponentBase;
use Input;
use Mail;
use Validator;
use Redirect;

class ContactForm extends ComponentBase
{

    public function componentDetails(){
        return [
            'name' => 'Contact Form',
            'description' => 'Simple contact form'
        ];
    }


    public function onSend(){
        $validator = Validator::make(
            [
                'name' => Input::get('name'),
                'email' => Input::get('email')
            ],
            [
                'name' => 'required|min:5',
                'email' => 'required|email'
            ]
        );

        if($validator->fails()){
            return Redirect::back()->withErrors($validator);
        } else {
            $vars = ['name' => Input::get('name'), 'email' => Input::get('email'), 'content' => Input::get('content')];

            Mail::send('watchlearn.contact::mail.message', $vars, function($message) {

                $message->to('youremail@gmail.com', 'Admin Person');
                $message->subject('New message from contact form');

            });
        }

    }

}