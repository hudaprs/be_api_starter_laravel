<?php

namespace App\Repositories\Auth;

use App\Interfaces\Auth\AuthInterface;

use App\User;
use App\Http\Resources\User\UserResource;
use App\Traits\ResponseFormatterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Mail\Message;
use DB, Validator, Hash, Mail, Str;


class AuthRepository implements AuthInterface
{
    use ResponseFormatterTrait;

    /**
     * Register new user to server
     * 
     * @param Request Illuminate\Http\Request
     * 
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|max:50',
            'email' => 'required|max:50|email|unique:users,email',
            'password' => 'required|min:8|max:50',
            'password_confirmation' => 'required|same:password'
        ]);

        DB::beginTransaction();
        try {
            $newUser = User::create([
                'name' => $request->name,
                'email' => preg_replace('/\s+/', '', strtolower($request->email)),
                'password' => \Hash::make($request->password)
            ]);

            // Verify user
            $emailVerificationToken = Str::random(30);
            DB::table('user_verifications')
            ->insert([
                'user_id' => $newUser->id,
                'token' => $emailVerificationToken
            ]);

            // Send email
            $subject = 'Please verify your email address';
            Mail::send('verify', [
                'name' => $newUser->name,
                'email_verification_token' => $emailVerificationToken
            ], function($mail) use($newUser, $subject) {
                $mail->from('modernportfolioofficial@gmail.com', "Modern Portfolio");
                $mail->to($newUser->email, $newUser->name);
                $mail->subject($subject);
            });

            DB::commit();
            return $this->success('Register success, please check your email for verify your account', $newUser, 201);
        } catch(\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), $e->getCode());
        }
    } 

    /**
     * Verify new registered user
     * 
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function verifyUser()
    {
        $emailVerificationToken = request()->token;
        DB::beginTransaction();
        try {
            $isRegistered = DB::table('user_verifications')
            ->where('token', $emailVerificationToken)
            ->first();

            if($isRegistered) {
                $user = User::find($isRegistered->user_id);

                // Check if user already verified
                if($user->is_verified == 1) return $this->success('Account already verified', null, 200);

                // Update user is_verified to 1
                $user->is_verified = 1;
                $user->save();
                
                // Delete record in user_verifications table
                DB::table('user_verifications')
                ->where('token', $emailVerificationToken)
                ->delete();

                // Send email for successfully registered
                Mail::send('verified', [
                    'name' => $user->name
                ], function($mail) use($user) {
                    $mail->from('modernportfolioofficial@gmail.com', "Modern Portfolio");
                    $mail->to($user->email, $user->name);
                    $mail->subject('Email has been verified');
                });

                DB::commit();
                return $this->success('You has been successfully verified your email address', $user, 200);
            } else {
                return $this->error('Verification token is invalid', 400);
            }
        } catch(\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function login()
    {
        try {
            $credentials = request(['email', 'password']);
            $credentials['is_verified'] = 1;        

            if (!$token = auth()->attempt($credentials)) return $this->error('Wrong email or password, or maybe your account is not verified yet', 401);

            return $this->respondWithToken($token);
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Recover password from registered user
     * 
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function recoverPassword()
    {
        DB::beginTransaction();
        try {
            // Check for existed user with email
            $user = User::where('email', strtolower(request()->email))->where('is_verified', '1')->first();
            if(!$user) return $this->error('Email not found', 404);

            // Verify user
            $passwordResetToken = Str::random(30);
            DB::table('user_verifications')
            ->insert([
                'user_id' => $user->id,
                'token' => $passwordResetToken,
            ]);

            // Send recovery email
            Mail::send('recover_password', [
                'name' => $user->name,
                'password_reset_token' => $passwordResetToken,
                'type' => 'recover'
            ], function($mail) use($user) {
                $mail->from('modernportfolioofficial@gmail.com', "Modern Portfolio");
                $mail->to($user->email, $user->name);
                $mail->subject('Recover password');
            });
            
            DB::commit();
            return $this->success('Recover password request has been send to your email', null, 200);
        } catch(\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Reset password from registered user
     * 
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function resetPassword()
    {
        request()->validate([
            'password' => 'required|min:8|max:50',
            'password_confirmation' => 'required|same:password'
        ]);

        $resetPasswordToken = request()->token;
        DB::beginTransaction();
        try {
            $isRegistered = DB::table('user_verifications')
            ->where('token', $resetPasswordToken)
            ->first();

            if($isRegistered) {
                $user = User::find($isRegistered->user_id);

                // Update user password
                $user->password = \Hash::make(request()->password);
                $user->save();
                
                // Delete record in user_verifications table
                DB::table('user_verifications')
                ->where('token', $resetPasswordToken)
                ->delete();

                // Send email for successfully registered
                Mail::send('recover_password', [
                    'name' => $user->name,
                    'type' => 'reset'
                ], function($mail) use($user) {
                    $mail->from('modernportfolioofficial@gmail.com', "Modern Portfolio");
                    $mail->to($user->email, $user->name);
                    $mail->subject('Password has been resetted');
                });

                DB::commit();
                return $this->success('You successfully change password', $user, 200);
            } else {
                return $this->error('Verification token is invalid', 400);
            }
        } catch(\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success('A reset password has been sent to email', null, 200);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function me()
    {
        try {
            return $this->success('OK', auth()->user(), 200);
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function logout()
    {
        try {
            auth()->logout();
            return $this->success('Logout Success', null, 200);
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function refresh()
    {        
        try {
            return $this->respondWithToken(auth()->refresh(), 'Token refreshed');
        } catch(\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     * @method POST
     */
    public function respondWithToken($token, $message = null)
    {
        return $this->success($message ? $message : 'OK', [
            'user' => auth()->user(),
            'token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60
        ], 200);
    }
}