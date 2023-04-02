<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserResource;
use Socialite;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    //
    public function register(Request $request) {

    }

    public function login(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:3',
        ]);

        if($validator->fails()) {
            return response()->json([
                'errors' => [
                    'message' => $validator->errors()
                 ]
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if(!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Username or Password is invalid.'],
            ]);
        }

        return new UserResource($user);
    }

    public function logout(Request $request) {
        $id = $request->user()->currentAccessToken()->id;
        $request->user()->tokens()->where('id', $id)->delete();

        return response()->json([
            'message' => 'Logout Success'
        ], 200);
    }

    public function redirectToProvider($provider)
    {
        $validated = $this->validateProvider($provider);
        if(!is_null($validated)) {
            return $validated;
        }

        return Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
    }

    public function handleProviderCallback($provider)
    {
        $validated = $this->validateProvider($provider);
        if(!is_null($validated)) {
            return $validated;
        }

        try {
             $userProvider = Socialite::driver($provider)->stateless()->user();
        } catch(ClientException $e) {
            return response()->json([
                'error' => 'Invalid credentials provided.'
            ], 422);
        }

        $findUser = User::where('email', $userProvider->getEmail())->first();

        if($findUser === null) {
            $user = User::create([
                'name' => $userProvider->getName(),
                'email' => $userProvider->getEmail(),
                'password' => bcrypt($provider.'12345'),
                'email_verified_at' => now(),
            ]);

            $user->providers()->create([
                'provider' => $provider,
                'provider_id' => $userProvider->getId(),
                'avatar' => $userProvider->getAvatar(),
                'user_id' => $user->id
            ]);

            $user_role = Role::where('name', 'member')->first();
            if($user_role) {
                $user->assignRole($user_role);
            }

            return new UserResource($user);
        } else {

            $findUser->providers()->where('provider', $provider)->where('provider_id', $userProvider->getId())->first();

            if($findUser == null) {
                $findUser->providers()->create([
                    'provider' => $provider,
                    'provider_id' => $userProvider->getId(),
                    'avatar' => $userProvider->getAvatar(),
                    'user_id' => $findUser->id
                ]);
            }

            return new UserResource($findUser);
        }
    }

    protected function validateProvider($provider)
    {
        if(!in_array($provider, ['facebook', 'google'])) {
            return response()->json([
                'error' => 'Please login using '.$provider
            ], 422);
        }
    }
}

