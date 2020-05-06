<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\Auth\AuthInterface;

class AuthController extends Controller
{
    protected $authInterface;
    
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(AuthInterface $auth)
    {
        $this->middleware('auth:api', [
            'except' => [
                'login', 
                'register', 
                'verifyUser',
                'recoverPassword',
                'resetPassword'
                ]
            ]
        );
        $this->authInterface = $auth;
    }

    public function register(Request $request)
    {
        return $this->authInterface->register($request);
    }

    public function verifyUser()
    {
        return $this->authInterface->verifyUser();
    }

    public function login()
    {
        return $this->authInterface->login();
    }

    public function recoverPassword()
    {
        return $this->authInterface->recoverPassword();
    }

    public function resetPassword()
    {
        return $this->authInterface->resetPassword();
    }

    public function me()
    {
        return $this->authInterface->me();
    }

    public function logout()
    {
        return $this->authInterface->logout();
    }

    public function refresh()
    {
        return $this->authInterface->refresh();
    }
}
