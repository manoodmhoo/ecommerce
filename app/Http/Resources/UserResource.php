<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Sanctum\PersonalAccessToken;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $token = $this->createToken('secureToken')->plainTextToken;
        $personal_token = PersonalAccessToken::findToken($token);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'token_type' => 'Bearer',
            'token' => $token,
            'expires_in' => $personal_token->created_at->addMinute(config('sanctum.expiration')),
            'roles' =>  $this->roles->pluck('name') ?? [],
            'roles.permissions' => $this->getPermissionsViaRoles()->pluck('name') ?? [],
        ];
    }
}
