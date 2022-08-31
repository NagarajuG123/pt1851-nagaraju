<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CustomizeUserSidebar;
use App\Models\BrandStaticContent;
use App\Models\MarketsAvailableInternational;
use App\Models\MarketsAvailableDomestic;
use App\Models\MarketStatus;
use App\Utility\RegistrationUtility;
use App\Utility\BrandUtility;
use App\Transformers\BrandInfoTransformer;

class BrandInfoController extends Controller
{
    public $registrationUtility, $brandUtility;

    public function __construct()
    {
        $this->registrationUtility = app(RegistrationUtility::class);
        $this->brandUtility = app(BrandUtility::class);
    }
    public function tab($slug){
        $brand = $this->registrationUtility->brandBySlug($slug);
        $categories = [];
        if(!empty($brand->info[0])){
            $categories[] = [
            'name' => 'Brand Info',
            'value' => 'info',
            'index' => 0,
            ];
        }
        if(!empty($brand->sidebar->brand_pdf)){
            $categories[] = [
            'name' => 'Download Brand PDF',
            'value' => 'brand_pdf',
            'index' => 1,
            ];
        }   
        if(!empty($brand->latestStory())){
            $categories[]  = [
            'name' => 'Latest Stories',
            'value' => 'latest_stories',
            'index' => 2,
            ];
        }
        if(!empty($brand->whyIBought) && !empty($brand->whyIBought->content)){
            $categories[]  = [
            'name' => 'Why I Bought',
            'value' => 'why-i-bought',
            'index' => 3,
            ];
         }
        if(!empty($brand->executive) && !empty($brand->executive->content)){
            $categories[]  = [
            'name' => 'Executive Q&A',
            'value' => 'executive',
            'index' => 4,
            ];
        }
        if(!empty($brand->availableMarkets)){
            $availableMarket = $brand->availableMarkets;
            $marketsAvailableInternational = MarketsAvailableInternational::where('markets_available_international_brand_id',$brand->id)->exists();
            $marketsAvailableDomestic = MarketsAvailableDomestic::where('markets_available_domestic_brand_id',$brand->id)->exists();
            if(!empty($availableMarket) && ($marketsAvailableInternational || $marketsAvailableDomestic)){
                $categories[]  = [
                'name' => 'Available Markets',
                'value' => 'available-markets',
                'index' => 5,
                ]; 
            }
        }
       return response()->json([
            'categories' => $categories
        ]);  
 }
            
    public function info(Request $request){
        $slug = $request->query('slug');
        $data = $this->brandUtility->brandExist($slug);
        if($data['status'] == 200) {
            $brandId = $data['data']->id;
            $info = Brand::select('id','title','description','descriptor','type','image_video','descriptor','video_type','position')
                        ->where('user_id','=',$brandId)
                        ->orderBy('position','ASC')
                        ->groupBy('position')
                        ->get();
            return [
                'data' => BrandInfoTransformer::collection($info),
                'ga' => [
                    'tracking_code' => $data['data']->gaCode ?? null,
                    'view_code' => $data['data']->gaViewcode ?? null,
                    '1851_franchise' => env('GA_TRACKING_CODE'),
                ],
                'meta' => $this->brandUtility->getMeta($brandId, 'Brand',$slug),
            ]; 
        } else{
            return $data['message'];
        }
    
    }
    public function pdf(Request $request){
        $slug = $request->query('slug');
        $data = $this->brandUtility->brandExist($slug);
        if($data['status'] == 200) {
            $brandId = $data['data']->id;
            $pdf = CustomizeUserSidebar::where(['user_id' => $brandId])->first();
            $brandPdf = $pdf->brand_pdf ?? null;
            $data = null;
            $email = false;
            if(!empty($brandPdf)) {
                if ($pdf->access_pdf_by_email == 'Yes') {
                    $email = true;
                }
                $data = [
                    'media' => [
                        'type' => 'pdf',
                        'url' => env('AWS_S3_URL').'/brand/pdf/'.$brandPdf,
                        'path' => 'brand/pdf/'.$brandPdf,
                    ],
                    'email_popup' => $email,
                ];
                if(!empty($pdf->brand_feature_image)) {
                    $data['image'] = [
                        'type' => 'image',
                        'url' => env('AWS_S3_URL').'/brand/featureImage/'. $pdf->brand_feature_image,
                        'path' => 'brand/featureImage/'. $pdf->brand_feature_image,
                    ];
                }
            }
            return response()->json([
                'data' => $data
            ]);
        } else {
            return $data['message'];
        }
    }
    public function whyIBought(Request $request){
        $slug = $request->query('slug');
        $data = $this->brandUtility->brandExist($slug);
        if($data['status'] == 200) {
            $brandId = $data['data']->id;
            $whyIBought = BrandStaticContent::where('brand_id','=', $brandId)
                            ->where('content_type','=',BrandStaticContent::TYPE_WHY_I_BOUGHT)
                            ->status()
                            ->first();
            if(!empty($whyIBought->content)) {
                if($whyIBought->image){
                    $media = [
                        'type' => 'image',
                        'url' =>  env('AWS_S3_URL').$whyIBought->image,
                        'path' => $whyIBought->image,
                    ];
                }
                return [
                    'data' => [
                        'name' => $data['data']->company ?? null,
                        'title' => $whyIBought->title ?? null,
                        'media' => $media ?? null,
                        'content' => $whyIBought->content ?? null,
                        'visible' => $whyIBought->visible ?? null,
                    ],
                    'meta' => $this->brandUtility->getMeta($brandId, 'Why_I_Bought',$slug),
                ];
            } else{
                return ['data' => null];
            }
        } else{
            return $data['message'];
        }
    }
    public function executive(Request $request){
        $slug = $request->query('slug');
        $data = $this->brandUtility->brandExist($slug);
        if($data['status'] == 200) {
            $brandId = $data['data']->id;
            $executive = BrandStaticContent::where('brand_id','=', $brandId)
                            ->where('content_type','=',BrandStaticContent::TYPE_EXECUTIVE)
                            ->status()
                            ->first();
            if(!empty($executive->content)) {
                if($executive->image){
                    $media = [
                        'type' => 'image',
                        'url' =>  env('AWS_S3_URL').$executive->image,
                        'path' => $executive->image,
                    ];
                }
                return [
                    'data' => [
                        'name' => $data['data']->company ?? null,
                        'title' => $executive->title ?? null,
                        'media' => $media ?? null,
                        'content' => $executive->content,
                        'visible' => $executive->visible,
                    ],
                    'meta' => $this->brandUtility->getMeta($brandId,'Executive',$slug),
                ];
            } else{
                return ['data' => null];
            }
        } else{
             return $data['message'];
        }
    }
    public function availableMarket(Request $request){
        $slug = $request->query('slug');
        $data = $this->brandUtility->brandExist($slug);
        if($data['status'] == 200) {
            $brandId = $data['data']->id;
            $availableMarket = BrandStaticContent::where('brand_id','=', $brandId)
                            ->where('content_type','=',BrandStaticContent::TYPE_AVAILABLE_MARKET)
                            ->status()
                            ->first();
            $internationals = MarketsAvailableInternational::join('country','country_id','=','markets_available_international_country_id')
                ->select(['markets_available_international_country_id', 'country.country_id','country.country_name','country.country_iso2_code' ])
                ->where(['markets_available_international_brand_id' => $brandId])
                ->get();
            if(!empty($internationals)) {
                $countries = $this->brandUtility->countryDetails($internationals);
            }
            $domestics = MarketsAvailableDomestic::join ('market_status', 'market_status.market_status_id','=','markets_available_domestic.markets_available_domestic_status_id')
                ->join('state','state_id','=','markets_available_domestic_state_id')
                ->select(['markets_available_domestic_status_id','market_status_name','market_status_color','state_name','state_iso2_code','state_id'])   
                ->where(['markets_available_domestic_brand_id' => $brandId])
                ->distinct()
                ->get()
                ->toArray();
            if (!empty($domestics)){
                $marketNames = $this->brandUtility->stateDetails($domestics,$brandId);               
            }
            return $data = [
                'data' => [
                    'name' =>  $data['data']->company ?? null,
                    'title' => $availableMarket->title ?? null,
                    'content' => $availableMarket->content ?? null,
                    'visible' => $availableMarket->visible ?? null,
                    'available-markets' =>  $marketNames ?? [],
                    'international-markets' => $countries ?? [],
                ],
                'meta' => $this->brandUtility->getMeta($brandId,'Available Markets',$slug),
            ];
        } else{
            return $data['message'];
        }    
    }
}