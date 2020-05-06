<?php

namespace App\Interfaces\Auth;

use Illuminate\Http\Request;

interface AuthInterface
{
    /**
     * Register new user to server
     * 
     * @param Request \Illuminate\Http\Request;
     * 
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function register(Request $request);
    
    /**
     * Verify new registered user to server
     * 
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function verifyUser();
    
    /**
     * Make request to server to logged in
     * 
     * @return \Illimunate\Http\JsonResponse
     * @method POST
     */
    public function login();

    /**
     * Recover password from registered user
     * 
     * @return \Illimunate\Http\JsonResponse
     * @method POST
     */
    public function recoverPassword();
    
    /**
     * Reset password from registered user
     * 
     * @return \Illimunate\Http\JsonResponse
     * @method POST
     */
    public function resetPassword();

    
    /**
     * Get the authenticated user
     * 
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function me();

    /**
     * Log the user out
     * 
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function logout();

    /**
     * Refresh a token
     * 
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function refresh();

    /**
     * Get the token response
     *
     * @param string $token
     * @param string $message
     *  
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithToken($token, $message = null);
}