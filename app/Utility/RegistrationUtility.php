<?php

namespace App\Utility;

use App\Models\Registration;
use Illuminate\Support\Str;

class RegistrationUtility
{
    public function getDetails($id){
       return Registration::where(['id' => $id])->first();
    }

    public function brandBySlug($slug){
        return Registration::where('brand_url','=',$slug)->active()->approve()->first();
    }
   
    public function brandInfo($brand){
        if (!empty($brand)) {
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
}