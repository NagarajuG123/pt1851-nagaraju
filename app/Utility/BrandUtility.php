<?php

namespace App\Utility;

use App\Models\UserCategory;
use App\Models\Registration;
use App\Models\BrandFeatureImage;
use App\Utility\RegistrationUtility;
use App\Models\MarketsAvailableDomestic;
use App\Models\Publication;
use App\Models\UserMetaTags;
use App\Models\Seotag;
use App\Utility\MetaUtility;
use App\Utility\CommonUtility;

class BrandUtility
{
    public $commonUtility,$metaUtility,$registrationUtility;
    public function __construct()
    {
        $this->metaUtility = app(MetaUtility::class);
        $this->commonUtility = app(CommonUtility::class);
        $this->registrationUtility =  app(RegistrationUtility::class);
    }
    public function fetchBySlug($slug) {
        return Registration::select(['id', 'first_name', 'last_name', 'company', 'brand_url', 'brandLogo', 'photo', 'status', 'is_deleted', 'gaCode', 'gaViewcode','facebook_page'])->where(['brand_url'=> $slug])->active()->approve()->first();
    }
    public function getDetails($brandId,$article)
    {
        $registration = new RegistrationUtility();
        if (!empty($brandId) && $brandId != UserCategory::BRAND_1851) {
            $brand =  $registration->getDetails($brandId);
            $data = [
                'id' => $brand->id,
                'name' => $brand->company,
                'slug' => $brand->brand_url,
                'fb_page_url' => $brand->facebook_page ?? null,
                'ga' => [
                    'tracking_code' => $brand->gaCode ?? null,
                    'view_code' => $brand->gaViewcode ?? null,
                    '1851_franchise' => env('GA_TRACKING_CODE'),
                ],
            ];
        } else {
            $data = [
                'id' => '1851',
                'name' => '1851',
                'slug' => '1851',
                'ga_code' => env('GA_TRACKING_CODE'),
            ];
        }
      
        return $data;
    }

    public static function brandFeatureImage($id){
        $query = BrandFeatureImage::join('registration','registration.id','=','brand_feature_image.user_id')
                ->where('user_id','=',$id)
                ->first();
        return $query;   
    }
    public function brandExist($slug){
        if(!empty($slug)) {
            $brand = $this->registrationUtility->brandBySlug($slug);
            if(!empty($brand)) {
                $data = [
                    'status' => 200,
                    'data' => $this->registrationUtility->brandBySlug($slug) ?? null
                ];
            } else{
                $data = [
                    'status' => 500,
                    'message' => 'Brand not found'
                ];
            }
        } else { 
            $data = [
                'status' => 500,
                'message' => 'Please check the endpoint URL'
            ];
        }
        return $data;
    }
    public function getMeta($userId, $pageType,$slug)
    {
        $meta = $this->commonUtility->publication();
        $brand = $this->commonUtility->brand($slug);
        $userMetaTags = UserMetaTags::where('userId','=',$userId)->where('page_type', 'Brand_Main')->first();
        $seoTags = Seotag::where('user_id','=',$userId)->first();
        if(!empty($userMetaTags)){
            $metaDescription = $userMetaTags->description;
            $metaTitle = $userMetaTags->title;
        }
        $article = $this->brandFeatureImage($brand->id);
        if ($article) {
            $brandFeature = BrandFeatureImage::select(['feature_image', 'videoImage', 'type'])
                               ->where('user_id','=', $userId)
                                ->first();
            $title = $metaTitle ?? $seoTags->title ?? $meta->title;
            $description = $metaDescription ?? $seoTags->description ?? $meta->description;
            $keywords = $userMetaTags->keywords ?? $seoTags->descriptor ?? $meta->keywords;
            $ogTitle = $metaTitle ?? $seoTags->title ?? null;
            $ogDescription = $metaDescription ?? $seoTags->description ?? null;
            $media['type'] ='image';
            $media['url'] = $brandFeature->type == 'image' ? env('AWS_S3_URL').'/brand/featureImage/' . $brandFeature->feature_image :  env('AWS_S3_URL').'/brand/featureImage/' . $brandFeature->videoImage;
            $media['path'] = $brandFeature->type == 'image' ?'/brand/featureImage/' . $brandFeature->feature_image : '/brand/featureImage/' . $brandFeature->videoImage;
            $data = $this->metaUtility->details($title,  $description, $keywords, 
                            $meta->author, $meta->robots, $index=null, $ogTitle, $ogDescription, 
                            $meta->ogSiteName, $ogUrl=null,$media
                            );
            return $data;
        }
    }
   
    public function countryDetails($internationals)
    {
        $details = [];
        foreach ($internationals as $international) {
             $details [] = [
                'id' => $international->country_id,
                'name' => $international->country_name,
                'iso2_code' => $international->country_iso2_code,
            ];
        }
        return $details;
    }
    public function stateDetails($domestics,$brandId)
    {
        foreach ($domestics as $domestic) {
            $states[$domestic['market_status_name'].'|'.$domestic['market_status_color']][]= $domestic;
        }
        foreach($states as  $index => $result) {
            list($name, $color) = explode('|',$index);
            foreach($result as $response) {
                $data[] = [
                    'id' => $response['state_id'],
                    'name' => $response['state_name'],
                    'iso2_code' => $response['state_iso2_code']
                ];
            }
            $details[] = [
                'name' => $name ?? null,
                'color' => $color ?? null,  
                'countries' => $data ?? []
            ]; 
            $data = [];
        }

        return  $details;
    }
    
}   