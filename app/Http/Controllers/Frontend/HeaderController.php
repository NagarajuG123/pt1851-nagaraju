<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\UserCategory;
use App\Models\Publication;
use App\Models\Issue;
use App\Models\CustomizeUserSidebar;
use App\Models\ContactBrandMandatory;
use App\Models\OwnBrandMandatory;
use App\Models\Registration;
use App\Models\Category;
use App\Models\DynamicPageContents;
use App\Models\StoryStats;
use App\Utility\StoryUtility;

class HeaderController extends Controller
{
    public $storyUtility;
    public function __construct()
    {
        $this->storyUtility = app(StoryUtility::class);
    }
    public function index(Request $request)
    {
        $slug = $request->query('slug');
        $logoUrl = "/";
        $siteName = Publication::where(['uniqueId' => env('SITE_ID')])->first();
        $publications = Publication::whereNotIn('uniqueId', [env('SITE_ID')])->get();
        foreach($publications as $publication)
        {
          $logo =  env('IMAGE_PROXY_URL') . '/static/' .$publication->uniqueId.'_header.png';
          if($publication->uniqueId  == 'Stachecow')
            {
               $logo = env('IMAGE_PROXY_URL'). '/static/' .$publication->uniqueId. '_header.svg';
            } 
            $otherPublications[] = [
                'id'=> $publication->uniqueId,
                'name' => $publication->name,
                'url' => $publication->url ?? null,
                'logo' =>  $logo
            ];
        }
        $name = $siteName->name;
        if($siteName->uniqueId == '1851') {
            $name = '1851';
        }
                $publication = [
                    "id" => $siteName->uniqueId,
                    "name" => $siteName->name, 
                    'trendingLogo' => env('S3_URL') . '/'.$siteName->trendingLogo,
                ];
                $logoImage = env('IMAGE_PROXY_URL') .'/static/'. $siteName->logo;
                $socialMedia = [
                    [
                        'url' => $siteName->twitter,
                        'media' =>  [
                            'image' =>  env('S3_URL') . "/twitter_header.png",
                            'width' => 14,
                            'height' => 11,
                        ],
                    ],
                    [
                        'url' => $siteName->facebook,
                        'media' =>  [
                            'image' => env('S3_URL') . "/facebook_header.png",
                            'width' => 5,
                            'height' => 12,
                        ],
                    ],
                    [
                        'url' => $siteName->instagram,
                        'media' =>  [
                            'image' =>  env('S3_URL') . "/instagram_header.png",
                            'width' => 12,
                            'height' => 13,
                        ],
                    ],
                    [
                        'url' => $siteName->linkedin,
                        'media' =>  [
                            'image' => env('S3_URL') . "/linkedin_header.png",
                            'width' => 12,
                            'height' => 12,
                        ],
                    ],
                    [
                        'url' => $siteName->youtube,
                        'media' =>  [
                            'image' =>  env('S3_URL') . "/youtube_header.png",
                            'width' => 12,
                            'height' => 10,
                        ],
                    ],
                    
                ];
                $menus = [
                    [
                        "title" => "About " . $name ,
                        "url" => "/about",
                        "isExternalUrl" => false 
                    ], 
                ];
                $baseUrl = env('API_URL');
            
           $issue = Issue::select(['id', 'articles_count', 'feature_image', 'monthYear'])
            ->where('articles_count', '>=', 1)
            ->orderBy('id', "DESC")->first();
            if(!empty($issue->feature_image)) {
                $monthlyCover = [
                    "type" => "image",
                    "url" =>  env('IMAGE_PROXY_URL'). '/covers/' . $issue->feature_image,
                    "coverUrl" => $issue->coverUrl(),
                    "path" => '/covers/' . $issue->feature_image,
                ];
            }
        $height = 54;
        $width = 54;
        $subcribeTitle = "SUBSCRIBE to our newsletter";
        $subcribeUrl = "/subscribe"; 
        $isInquiry = false;
        if($slug) {
            $brand = Registration::select('id','brand_url','status', 'company', 'brandLogo', 'photo', 'navBrandTitle', 'navBrandCustTitle')
            ->where('brand_url','=',$slug)
            ->active()->approve()
            ->first();
            if(!empty($brand)){
                $logoImage = env('IMAGE_PROXY_URL').'/brand/logo/'.$brand->brandLogo;
                if (empty($brand->brandLogo)) {
                    $logoImage = env('IMAGE_PROXY_URL').'/brand/logo/'.$brand->photo;
                }
                if(empty($brand->brandLogo) && empty($brand->photo) && env('SITE_ID') == $siteName->uniqueId) {
                    $logoImage = env('IMAGE_PROXY_URL'). '/static/EElogo.png' ;
                }
                $logoUrl = "/". $brand->brand_url;
                $height = 80;
                $width = 100;

                $subcribeTitle = $brand->company;
                $subcribeUrl = null;
                $dynamicData = CustomizeUserSidebar::where('user_id', $brand->id)->first();

                $articles = $this->getBrandArticles($brand) ?? null;

                $sidebar = [
                    'detail' => [
                        'id' => $brand->id,
                        'name' => $brand->company,
                        'slug' => $brand->brand_url
                    ],
                    'login' => env('BRAND_URL')."/site/login.html",   
                    'about' => '/'.$brand->brand_url.'/info'
                ];
                if(!empty($dynamicData)) {
                    $actions = $this->getBrandActions($dynamicData);
                    $sidebar['socialMedia'] = $this->getBrandSocialMedias($dynamicData);
                    $socialMedia = $this->getBrandHeaderSocialMedias($dynamicData);
                }
                $contact = ContactBrandMandatory::select('user_id')
                    ->where('user_id', $brand->id)
                    ->first();
                if(!empty($contact)) {
                    $sidebar['contact'] = ''; 
                }
              $inquireModel = OwnBrandMandatory::select('user_id')
                    ->where('user_id', $brand->id)
                    ->first();
                if(!empty($inquireModel) && $brand->navBrandTitle !== 'None') {
                    $menuTitle = $brand->navBrandTitle;
                    if ($brand->navBrandTitle == 'Customize') {
                        $menuTitle = $brand->navBrandCustTitle;
                    }
                    $isInquiry = true;
                }
        
               
                if(!empty($dynamicData->brand_pdf)) {
                     $emailPopup = false;
                    $pdfPath = env('AWS_S3_URL') .'/brand/pdf/'.$dynamicData->brand_pdf;
                    if ($dynamicData->access_pdf_by_email == 'Yes') {
                        $emailPopup = true;
                    }
                    $sidebar['pdf'] = [
                        'url' => $pdfPath,
                        'emailPopup' => $emailPopup
                    ];
                }
                if(!empty($dynamicData->website)) {
                    $sidebar['website'] = $dynamicData->website;
                }
                if(!empty($articles)) {
                    $sidebar['articles'] = $articles ?? null;
                }
                $menus = [
                    ['title' => 'Brand Information',
                    'url' => '/'.$brand->brand_url.'/info',
                    'isExternalLink' => false]
                ];
            } else {
                return response()->json([
                    "status" => 404,
                    'message' => "Brand not found"
                ]);
            }
        } else {
            $actions = $this->getMainActions();
            $spotlights = [];
            $categories = Category::select('id', 'categories', 'slug')
                ->whereNotNull('sortId')
                ->orderBy('sortId', 'ASC')
                ->get();
            foreach($categories as $category) {
                $articleCount = UserCategory::select('id','title','user_id','admin_cat_id','approve','is_trash','post_status')
                            ->where('admin_cat_id','=',$category->id)
                            ->publish()
                            ->approve()
                            ->active()
                            ->postByAuthor()
                            ->count();
                if($articleCount !== 0){
                    $spotlights[] = [
                        'name' => $category->categories,
                        'url' => '/'.$category->slug,
                    ];
                }
            }
            $content = DynamicPageContents::select(['dynamic_page_contents_featured', 'dynamic_page_contents_title', 'dynamic_page_contents_url'])->where('dynamic_page_contents_featured', DynamicPageContents::FEATURED)
            ->first();

            $sidebarMenus = $this->getSidebarMenus($name);
            $awards = [
                [
                    'title' => $content->dynamic_page_contents_title ?? null,
                    'url' => $content->dynamic_page_contents_url ?? null,
                ],
                [
                    'title' => 'Website Awards',
                    'url' => '/franchisedevelopmentawards',
                ],
            ];
            $sidebar = [
                "categories" => $spotlights,
                "awards" => $awards,
                "menus" => $sidebarMenus
            ];
        }
          $data = [
              "publication" => $publication,
              'otherPublication' => $otherPublications,
                "logo" => [
                    "width" => $width,
                    "height" => $height,
                    "url" => $logoUrl,
                    "image" => $logoImage
                ],
                "subscribe" => [
                    "title" => $subcribeTitle,
                    "subTitle" => "Franchise news powered by ",
                    "url" => $subcribeUrl
                ],
                "socialMedia" => $socialMedia,
                "monthlyCover" => $monthlyCover ?? null,
                "menus" => $menus ?? null,
                "actions" => $actions ?? null,
                "sidebar" => $sidebar ?? null
            ];
            if($isInquiry) {
                $data['inquire'] = $menuTitle;
            }
            return response()->json([
                'data' => $data
            ]);
    }

    public function getMainActions() {
            $adviceMenuDatas = CustomizeUserSidebar::select(['id','section_title','linkTitle6', 'linkUrl6', 'linkTitle7', 'linkUrl7', 'linkTitle8', 'linkUrl8',
                'linkTitle9', 'linkUrl9', 'linkTitle10', 'linkUrl10', 'linkTitle11', 'linkUrl11', 'linkTitle12', 'linkUrl12', 'isExternalLink6', 'isExternalLink7', 'isExternalLink8', 'isExternalLink9', 'isExternalLink10', 'isExternalLink11', 'isExternalLink12'])->where('user_id', '0')->get();
            if(!empty($adviceMenuDatas)) {
                $actions = [];
                foreach ($adviceMenuDatas as $adviceMenuData) {
                    $items = [];
                    foreach (range(6, 12) as $i) {
                        $titlekey = 'linkTitle'.$i;
                        $urlKey = 'linkUrl'.$i;
                        $externalLink = 'isExternalLink'.$i;
                        if ($adviceMenuData->$titlekey && $adviceMenuData->$urlKey) {
                            $item = [
                                'title' => $adviceMenuData->$titlekey,
                                'url' => $adviceMenuData->$urlKey,
                                'isExternalLink' => $adviceMenuData->$externalLink ? true : false,
                            ];
                            array_push($items, $item);
                        }
                    }
                    $data = [
                        'title' =>$adviceMenuData->section_title,
                        "items" => $items
                    ];
                    array_push($actions, $data);
                }
            }
          return $actions;
        }
    
    public function getSidebarMenus($name) {
        return [
            
            [
                'title' => 'Power Rankings',
                'url' => '/powerrankings'
            ],
            [
                'title' => 'About '. $name,
                'url' => '/about'
            ],
            [
                'title' => 'Sponsored Content',
                'url' => '/termsofuse',
                'fragment' => 'sponsored'
            ],
            [
                'title' => 'Advertise on '.$name,
                'url' => '/about',
                'fragment' => 'tellus'
            ],
            [
                'title' => 'Contact '.$name.' Editorial',
                'url' => '/contact-editorial'
            ],
            [
                'title' => 'Terms of Use',
                'url' => '/termsofuse'
            ],
            [
                'title' => 'Login',
                'url' => env('BRAND_URL')."/site/login.html"    
            ]
        ];

    }
    public function getBrandActions($dynamicData){
        $items = [];
        for ($i = 6,$j = 1; $i <= 12; $i++,$j++) {
            if (!empty($dynamicData['linkTitle'.$i])) {
                $item = [
                    'title' => $dynamicData['linkTitle'.$i],
                    'url' => $dynamicData['linkUrl'.$i],
                ];
                array_push($items, $item);
            }
        }
        $data[] = [
            'title' => "Learn",
            "items" => $items
        ];
        
        return $data;
    }
     public function getBrandHeaderSocialMedias($dynamicData) {
        $items = [];
        for ($i = 1,$j = 1; $i <= 5; $i++,$j++) {
            if (!empty($dynamicData['linkTitle'.$i])) {
                $item = [
                    'title' => $dynamicData['linkTitle'.$i],
                    'url' => $dynamicData['linkUrl'.$i],
                    'media' => $this->getHeaderImage($dynamicData['linkTitle'.$i]),
                ];
                array_push($items, $item);
            }
        }
        
        return $items;
    }
    public function getBrandSocialMedias($dynamicData) {
        $items = [];
        for ($i = 1,$j = 1; $i <= 5; $i++,$j++) {
            if (!empty($dynamicData['linkTitle'.$i])) {
                $item = [
                    'title' => $dynamicData['linkTitle'.$i],
                    'url' => $dynamicData['linkUrl'.$i],
                    'media' => $this->getImage($dynamicData['linkTitle'.$i]),
                ];
                array_push($items, $item);
            }
        }
        
        return $items;
    }
    public function getBrandArticles($brand) {
        $stories = StoryStats::select('story_id', DB::raw("SUM(page_views) as page_views"), 'brand_id')
        ->where('brand_id', $brand->id)
        ->orderBy('page_views', 'DESC')
        ->groupBy(['story_id', 'brand_id'])
        ->limit(3)->get();
        $result  = $stories->map->only(['story_id']);

       
        $articles = UserCategory::select(['id', 'title'])
            ->whereIn('id', $result)
            ->get();
        
        foreach($articles as $article) {
            $data[] = [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $this->storyUtility->slug($brand, $article)
            ];
        }
        return $data ?? null;
    }

    public function getImage($title){
        $image = [];
        if(str_contains($title,'Facebook')){
            $image = [
               'image' => env('S3_URL') . "/facebook.png",
               'width' => 7,
               'height' => 15,
            ];
        }
        else if(str_contains($title,'Instagram')){
            $image = [
                'image' =>  env('S3_URL') . "/instagram.png",
                'width' => 15,
                'height' => 15,
            ];
        }
        else if(str_contains($title,'Linkedin')){
            $image = [
                'image' => env('S3_URL') . "/linkedin.png",
                'width' => 16,
                'height' => 12,
            ];
        }
        else if(str_contains($title,'Youtube')){
            $image = [
                'image' =>  env('S3_URL') . "/youtube.png",
                'width' => 16,
                'height' => 11,
            ];
        }
        else if(str_contains($title,'Twitter')){
            $image = [
                'image' =>  env('S3_URL') . "/twitter.png",
                'width' => 16,
                'height' => 12,
            ];
        }
        return $image;
    }
    public function getHeaderImage($title){
       
        $image = [];
        if(strtolower(trim($title)) == 'facebook'){
            $image = [
               'image' => env('S3_URL') . "/facebook_header.png",
               'width' => 5,
               'height' => 12,
            ];
        }
        else if(strtolower(trim($title)) == 'instagram'){
            $image = [
                'image' =>  env('S3_URL') . "/instagram_header.png",
                'width' => 12,
                'height' => 13,
            ];
        }
        else if(strtolower(trim($title)) == 'linkedin'){
            $image = [
                'image' => env('S3_URL') . "/linkedin_header.png",
                'width' => 12,
                'height' => 12,
            ];
        }
        else if(strtolower(trim($title)) == 'youtube'){
            $image = [
                'image' =>  env('S3_URL') . "/youtube_header.png",
                'width' => 12,
                'height' => 10,
            ];
        }
        else if(strtolower(trim($title)) == 'twitter'){
            $image = [
                'image' =>  env('S3_URL') . "/twitter_header.png",
                'width' => 14,
                'height' => 11,
            ];
        }
        return $image;
    }
}