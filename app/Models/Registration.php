<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BrandStaticContent;

class Registration extends Model
{
    const STATUS_APPROVE = 'approve';
    const STATUS_DISAPPROVE = 'disapprove';
    public $table = 'registration';

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
    public function scopeApprove($query)
    {
        return $query->where('status', self::STATUS_APPROVE);
    }
    public function getGoogleCode()
    {
        return [
                    'tracking_code' => $this->gaCode ?? null,
                    'view_code' => $this->gaViewcode ?? null,
                    'gtm_code' => $this->gtm ?? null,
                    '1851_franchise' => env('GA_TRACKING_CODE'),
                ];
    }
    public function getMediaUrl()
    {
        $url = env('AWS_S3_URL') . '/author/' . $this->photo;
        $image = $this->photo;
        if (empty($image)) {
            $url = env('AWS_S3_URL') . '/author/no-image.png';
            $image = 'no-image.png';
        }
        return [
            'type' => 'image',
            'url' => $url,
            'path' => 'author/'.$image,
        ];
    }
    
    public function getSocialMedia() {
        $medias = [];
        if(!empty($this->facebook_link)) {
           $medias[] =
            [
                'title' => 'Facebook',
                'url' => $this->getSocialLink($this->facebook_link),
                'index' => 0
            ];
        }
        if(!empty($this->twitter_link)) {
            $medias[] =
            [
                'title' => 'Twitter',
                'url' => $this->getSocialLink($this->twitter_link),
                'index' => 1
            ];
        }
        if(!empty($this->instagram_link)) {
            $medias[] =
            [
                'title' => 'Instagram',
                'url' => $this->getSocialLink($this->instagram_link),
                'index' => 2
            ];
        }
        if(!empty($this->linkedin_link)) {
            $medias[] =
            [
                'title' => 'Linkedin',
                'url' => $this->getSocialLink($this->linkedin_link),
                'index' => 3
            ];
        }
        return $medias;
    }
    public function getSocialLink($link){
        $parse = parse_url($link);
       
        if(isset($parse['scheme']) && in_array($parse['scheme'],['http','https'])){
            return $link;
        }
        else{
            return 'https://' . $link;
        }
    }

    public function info() {
        return $this->hasMany('App\Models\Brand','user_id');
    }
    public function sidebar() {
        return $this->hasOne('App\Models\CustomizeUserSidebar','user_id');
    }
    public function staticContent() {
        return $this->hasMany('App\Models\BrandStaticContent','brand_id')->where('visible', true);
    }
    public function whyIBought() {
        return $this->hasOne('App\Models\BrandStaticContent','brand_id')->where('visible', true)->where('content_type', BrandStaticContent::TYPE_WHY_I_BOUGHT);
    }
    public function executive() {
        return $this->hasOne('App\Models\BrandStaticContent','brand_id')->where('visible', true)->where('content_type', BrandStaticContent::TYPE_EXECUTIVE);
    }
    public function availableMarkets() {
        return $this->hasOne('App\Models\BrandStaticContent','brand_id')->where('visible', true)->where('content_type', BrandStaticContent::TYPE_AVAILABLE_MARKET);
    }
    public function latestStory() {
        return UserCategory::where('user_id',$this->id)
                ->publish()
                ->approve()
                ->active()
                ->orderBy('post_date', 'DESC')
                ->first();
    }
    public function featureImage() {
        return $this->hasOne('App\Models\BrandFeatureImage','user_id');
    }
    public function brandInfo()
    {
        return $this->hasOne('App\Models\BrandInfo','user_id');
    }
}
