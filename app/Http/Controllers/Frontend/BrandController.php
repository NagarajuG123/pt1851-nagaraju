<?php

namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Models\Registration;
use App\Models\DynamicPageContents;
use App\Models\FranchiseResearch;
use App\Models\Category;
use App\Models\Brand;
use App\Models\BrandInfo;
use App\Models\UserCategory;

use App\Utility\RegistrationUtility;
use App\Utility\BrandUtility;
use App\Utility\CommonUtility;

class BrandController extends Controller
{
    public $registrationUtility, $brandUtility,$commonUtility;

    public function __construct()
    {
        $this->registrationUtility = app(RegistrationUtility::class);
        $this->brandUtility = app(BrandUtility::class);
        $this->commonUtility = app(CommonUtility::class);

    }
    public function active(){
        $activeBrands = Registration::select(['id','company','brand_url','user_type','parent_id'])
                ->where([['user_type','=','user'],['parent_id','=', null]])
                ->active()
                ->approve()
                ->get();
        foreach($activeBrands as $user){
            $data[] = [
                'id' => $user->id,
                'name' => $user->company,
                'slug' => $user->brand_url,
            ];
        }
        return response()->json([
            'data' => $data
        ]);
    }

    public function franchiseResearch(Request $request){
        $slug = $request->query('slug');
        $data = null;
        $brand = Registration::select('id','brand_url')
                ->where('brand_url','=',$slug)
                ->first();
               
        $franchiseData = FranchiseResearch::where('brand_id','=',$brand->id)->orderBy('sort_id', 'ASC')->get();

        if(!empty($franchiseData)){
            foreach($franchiseData as $franchise){
                $path = !empty($franchise->image_path) ?  $franchise->image_path : 'brand/featureImage/1851_default.jpg';
                $imageUrl = !empty($franchise->image_path) ? env('IMAGE_PROXY_URL') .  $franchise->image_path : env('IMAGE_PROXY_URL') . '/brand/featureImage/1851_default.jpg';
                $defaultTitle = $this->getDefaultTitle($franchise->type);
                $data[] = [
                    'headline' => $franchise->headline ?? $defaultTitle,
                    'media' => [
                        'url' => $imageUrl,
                        'path' => $path,
                    ],
                    'description' => $franchise->description ?? null,
                    'url' => $franchise->page_url ?? null,
                    'isExternal' => $franchise->is_external ? true : false,
                ];
            }
        } 
        return ['data' => $data];
    }

    public function getDefaultTitle($type){
        $title = 'Brand Info';
        if($type == FranchiseResearch::WHY_I_BOUGHT){
            $title = 'Why I Bought';
        } elseif($type == FranchiseResearch::EXECUTIVE){
            $title = 'Executive';
        } elseif($type == FranchiseResearch::AVAILABLE_MARKET){
            $title = 'Available Markets';
        }
        return $title ?? null;
    }
  
    public function details($slug){
        $brand = $this->registrationUtility->brandBySlug($slug);
        if (!empty($brand)) {
            $data = [
                'id' => $brand->id,
                'name' => $brand->company,
                'slug' => $slug ?? null,
                'ga' => $brand->getGoogleCode(),
                'type' => 'brand_page',
            ];
        }
        $content = DynamicPageContents::where('dynamic_page_contents_url', '=', $slug)->first();
        if (!empty($content)) {
            $data = [
                'id' => '1851',
                'slug' => $slug ?? null,
                'type' => 'dynamic_page',
            ];
        }
        $category = Category::select('categories','slug')->where('slug', '=', $slug)->first();
        if(!empty($category)) {
            $data = [
            'id' => '1851',
            'slug' => $slug ?? null,
            'name' => $category->categories,
            'type' => 'category_page',
            ];
        } 
        if  ( empty($brand) && empty($content) && empty($category) )  {
            return response()->json([
                "status" => 404,
                'message' => "Brand not found"
            ]);
        }
        return $data;
 }
    public function financial(Request $request){
        $slug = $request->query('slug');
        $data = $this->brandUtility->brandExist($slug);
        if($data['status'] == 200) {
            $brand= $data['data'];
            if($brand->brandInfo){
                $optionNames = [
                    $brand->brandInfo->option_o ,$brand->brandInfo->option_t, $brand->brandInfo->option_tr,$brand->brandInfo->option_f 
                ];
                $optionValues = [
                    $brand->brandInfo->option_value_o ,$brand->brandInfo->option_value_t , $brand->brandInfo->option_value_tr ,$brand->brandInfo->option_value_f
                ];
                foreach ($optionNames as $key => $optionName) {
                    if (!empty($optionName && $optionValues[$key])) {
                        $info[] = [
                            'title' => $optionName ?? null,
                            'value' => $optionValues[$key] ?? null,
                        ];
                    }
                }
            }
            $path = null;
            $type = null;
            $image = null;
            if($brand->featureImage){
                $path = 'brand/featureImage/'.$brand->featureImage->feature_image ?? null;
                $type = $brand->featureImage->type ?? null;
                $image = $brand->featureImage->feature_image ?? null;
                if($brand->featureImage->type === 'video'){
                    $path = 'brand/featureImage/'.$brand->featureImage->videoImage ?? null;
                }
            }
            return [
                'data' => [
                    "id" => $brand->brandInfo->user_id ?? null,
                    "name"=> $brand->brandInfo->brand_name ?? null,
                    'slug' => $brand->brand_url,
                    "units"=> $brand->brandInfo->no_of_units_curr ?? null,
                    "expected_to_open"=> $brand->brandInfo->expected_to_open ?? null,
                    "startup_costs" => $brand->brandInfo->startup_cost ?? null,
                    "franchise_fee" => $brand->brandInfo->frenchise_fee ?? null,
                    "royalty" => $brand->brandInfo->royality ?? null,
                    "optional_info" => $info ?? [],
                    "more_info" => $brand->brandInfo->more_information ?? null,
                    "media" => app(CommonUtility::class)->media($type, $path,$image) ?? null,
                ]
            ];
        } else{
            return $data['message'];
        }
    }
}
