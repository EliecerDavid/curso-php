<?php

namespace App\Controllers;

use App\Models\User;
use Zend\Diactoros\Response\RedirectResponse;

class AuthController extends BaseController
{
    public function getLogin()
    {
        return $this->renderHTML('login.twig', compact('responseMessage'));
    }

    public function postLogin($request)
    {
        $responseMessage = null;

        $postData = $request->getParsedBody();

        $user = User::where('username', $postData['username'])
                    ->first();

        if ( ! $user) {
            $responseMessage = 'Bad credentials!!';
        } elseif ( ! password_verify($postData['password'], $user->password)) {
            $responseMessage = 'Bad credentials!!';
        } else {
            $_SESSION['userId'] = $user->id;
            return new RedirectResponse('/admin');
        }

        return $this->renderHTML('login.twig', compact('responseMessage'));
    }

    public function getLogout()
    {
        unset($_SESSION['userId']);
        return new RedirectResponse('/login');
    }
}
