<?php

class LogInForm extends BaseForm
{
    public function __construct()
    {
        $username = new \Europa\Form\Element\Text;
        $password = new \Europa\Form\Element\Password;

        $username->label = 'Username';
        $password->label = 'Password';

        $this['username'] = $username;
        $this['password'] = $password;

        $this->fill(
            array(
                'username' => 'username',
                'password' => 'password'
            )
        );
    }
}