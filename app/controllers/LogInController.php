<?php

class LogInController extends AbstractController
{
    /**
     * Displays a user form.
     * 
     * @return void
     */
    public function get()
    {
        
    }

    /**
     * Controller for authenticating a user.
     * 
     * @preFilter ParamFilter
     * 
     * @param string $username The user's username.
     * @param string $password The user's password.
     * 
     * @return void
     */
    public function post($username, $password)
    {
        // usually this is done in some sort of auth class
        if ($username === 'username' && $password === 'password') {
            $_SESSION['isLoggedIn'] = true;
            $this->redirect('index.php/private');
        }

        // good practice to redirect after posting
        $this->redirect('index.php/log-in');
    }
}