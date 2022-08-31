<?php

namespace App\Transformers;


use Illuminate\Http\Resources\Json\JsonResource;

use App\Utility\UserUtility;

class UserTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */

    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'first_name' =>(string) $this->adminProfile->first_name ?? null,
            'last_name' => (string) $this->adminProfile->last_name ?? null,
            'email' => (string) $this->adminProfile->email ?? null,
            'role' => app(UserUtility::class)->fetchRoleName($this->role_id) ?? null,
            'media' => [
                'type' => 'image',
                'url' => env('AWS_S3_URL') ."/".$this->image_path ?? null, 
                'path' =>  'admin/'.$this->image_path
            ]
        ];
    }
}