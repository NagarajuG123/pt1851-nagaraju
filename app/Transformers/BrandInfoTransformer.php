<?php

namespace App\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Utility\CommonUtility;


class BrandInfoTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */

    public function toArray($request): array
    {
        $path = 'brand/info/'.$this->image_video;
        if($this->type === 'video'){
            $path = 'brand/info/'.$this->video_type;
        }
        return [
            'title' => (string) $this->title ?? null,
            'short_description' =>(string) $this->type == 'photo_text' ? $this->descriptor : $this->description ,
            'media' =>  $this->image_video ? (app(CommonUtility::class)->media( $this->type, $path,$this->image_video)) : null
        ];
    }
}