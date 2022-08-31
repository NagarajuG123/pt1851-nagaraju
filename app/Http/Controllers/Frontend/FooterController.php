<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Models\Registration;
use App\Models\ContactBrandMandatory;
use App\Models\Category;
use App\Models\CustomizeUserSidebar;
use App\Models\UserCategory;

class FooterController extends Controller
{
    public function index(Request $request)
    {
        $slug = $request->query('slug');
        $site = Publication::where(['uniqueId' => env('SITE_ID')])->first();
        $publication[] = [
                "title" => '',
                "logo" => env('S3_URL'). '/mainland_footer.png',
                "url" => 'https://www.hellomainland.com',
                'width' => 140,
                'height' => 48,
        ];
        $allPublications = Publication::select(['width','height','name','url','uniqueId'])->get();
        foreach ($allPublications as $model)
        {
            $logo = env('IMAGE_PROXY_URL'). '/static/' .$model->uniqueId. '_footer.png';
            if(in_array($model->uniqueId, ['Stachecow', '1851']))
            {
               $logo = env('IMAGE_PROXY_URL'). '/static/' .$model->uniqueId. '_footer.svg';
            }
         $publication[] = 
             [
                 "title"  => $model->name,
                 "logo"   => $logo,
                 "url"    =>$model->url,
                 'width'  => $model->width,
                 'height' => $model->height,
             ];
            }
        $socialMedia = [
                [
                'url' => $site->facebook,
                'index' => 0,
                'media' =>  [
                    'image' => env('S3_URL') . "/facebook.png",
                    'width' => 6.96,
                    'height' => 15.06,
                ],
            ],
            [
                'url' =>  $site->instagram,
                'index' => 1,
                'media' =>  [
                    'image' =>  env('S3_URL') . "/instagram.png",
                    'width' => 15.36,
                    'height' => 15.34,
                ],
            ],
            [
                'url' =>  $site->linkedin,
                'index' => 2,
                'media' =>  [
                    'image' => env('S3_URL') . "/linkedin.png",
                    'width' => 14.73,
                    'height' => 14.72
                ],
            ],
            [
                'url' => $site->youtube,
                'index' => 3,
                'media' =>  [
                    'image' =>  env('S3_URL') . "/youtube.png",
                    'width' => 16,
                    'height' => 11,
                ],
            ],
            [
                'url' =>  $site->twitter,
                'index' => 4,
                'media' =>  [
                    'image' =>  env('S3_URL') . "/twitter.png",
                    'width' => 16.42,
                    'height' => 10.95,
                ],
            ],
        ];
        $categories = Category::select('id', 'categories', 'slug')
                ->whereNotNull('sortId')
                ->orderBy('sortId', 'ASC')
                ->get();
        if($slug) {
            $brand = Registration::select('id','brand_url','status', 'company', 'brandLogo', 'photo')
            ->where('brand_url','=',$slug)
            ->active()->approve()
            ->first();
              
            
            if(!empty($brand)){
                $dynamicData = CustomizeUserSidebar::where('user_id', $brand->id)->first();
            
            $items = [];
                for ($i = 1,$j = 1; $i <= 5; $i++,$j++) {
                    if (!empty($dynamicData['linkTitle'.$i])) {
                        $item = [
                            'title' => $dynamicData['linkTitle'.$i],
                            'url' => $dynamicData['linkUrl'.$i],
                            'index' => $this->getIndex($dynamicData['linkTitle'.$i]),
                        ];
                        array_push($items, $item);
                    }
                }
            $socialMedia   = $items;  
                $spotlights =  $this->getCategories($categories, $brand);
                $menus =[
                    [
                    'title' => 'Spotlight',
                    "items" => $spotlights ?? null
                ],
                [
                    'title' => 'About Us',
                    'url' => '/'.$brand->brand_url.'/info'
                ],
                
                [
                    "title" => 'Site Map',
                    'url' => '/'.$brand->brand_url.'/sitemap'
                ],
                [
                    "title" => 'Terms of use',
                    'url' => '/'.$brand->brand_url.'/termsofuse'
                ]
                ];
                $contact = ContactBrandMandatory::select('user_id')
                    ->where('user_id', $brand->id)
                    ->first();
                if(!empty($contact)) {
                    array_splice($menus, 2, 0,[[
                        'title' => 'Contact',
                        'url' => ''
                    ]]);
                }
            } else {
                return response()->json([
                    "status" => 404,
                    'message' => "Brand not found"
                ]);
            }
        } else {
            $spotlights = $this->getCategories($categories);
           
            $menus = [
                [
                    'title' => 'Spotlight',
                    "items" => $spotlights ?? null
                ],
                [
                    'title' => 'About Us',
                    'url' => '/about'
                ],
                [
                    "title" => 'Contact',
                    'url' => '/contact-editorial'
                ],
                [
                    "title" => 'Site Map',
                    'url' => '/sitemap'
                ],
                [
                    "title" => 'Terms of use',
                    'url' => '/termsofuse'
                ]
            ];
        }
         
            return response()->json([
                "publication" => $publication,
                "socialMedia" => $socialMedia,
                "menus" => $menus,
                "footerText" => $site->footerText ?? null
            ]);
    }
    public function getCategories($categories, $brand=null) {
        $spotlights = [];
        foreach($categories as $category) {
            $article = UserCategory::select('id','title','user_id','admin_cat_id','approve','is_trash','post_status')
                    ->where('admin_cat_id','=',$category->id)
                    ->publish()
                    ->approve()
                    ->active();
            if(!empty($brand)) {
                $article = $article->where('user_id','=',$brand->id)->where('user_author','!=',0);
            } else{
                $article = $article->postByAuthor();
            }
            $article =$article->get();
            $articleCount = $article->count();
            if($articleCount > 0) {
                if($articleCount > 2){  
                    $url = !empty($brand) ? '/'.$brand->brand_url. '/'.$category->slug : '/'.$category->slug;
                } else {
                    $categoryURL = $article[0]['title'];
                    $url = !empty($brand) ? '/'.$brand->brand_url. '/'.Str::slug($categoryURL) . '-' . $article[0]['id'] : '/'.Str::slug($categoryURL) . '-' . $article[0]['id'];
                }
                $spotlights[] = [
                    'name' => $category->categories,
                    'url' => $url,
                    'shortName' => str_replace(' Spotlight', '', $category->categories)
                ];
            }
        }
        return $spotlights;
    }
    public function getIndex($title){
        $index = 5;
        if(strtolower(trim($title)) == 'facebook'){
            $index = 0;
        }
        else if(strtolower(trim($title)) == 'instagram'){
            $index = 1;
        }
        else if(strtolower(trim($title)) == 'linkedin'){
            $index = 2;
        }
        else if(strtolower(trim($title)) == 'youtube'){
            $index = 3;
        }
        else if(strtolower(trim($title)) == 'twitter'){
            $index = 4;
        }
        return $index;
    }
}