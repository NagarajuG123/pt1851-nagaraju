<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\UserCategory;
use App\Models\Registration;
use App\Utility\CategoryUtility;
use App\Utility\RegistrationUtility;
use App\Utility\StoryUtility;
use App\Transformers\CategoryTransformer;


class CategoryController extends Controller
{
  public  $registrationUtility, $storyUtility, $categoryUtility;
  public function __construct()
    {
       
        $this->categoryUtility = app(CategoryUtility::class);
        $this->registrationUtility = app(RegistrationUtility::class);
        $this->storyUtility = app(StoryUtility::class);
    }
    public function index()
    {
      $categories = Category::select('id', 'categories','photo', 'description')
      ->orderBy('sortId', 'ASC')
      ->whereNotNull('sortId')
      ->get();
      foreach($categories as $category)
       {
            $data[] = [
              'id' => $category->id ?? null,
              'name' => $category->categories ?? null,
              'slug' => Str::slug($category->categories),
              'description' =>  $category->description ?? null,
              'media' =>$category->getMedia($category->photo) ?? null,
              ];
      }
      return $data;

    }
    public function tab(Request $request)
    {
        $slug = $request->query('slug');
        $categories =  $this->categoryUtility->allCategories();
        $brand = null;
        if(!empty($slug))
        {
          $brand = $this->registrationUtility->brandBySlug($slug);
        }
        foreach($categories as $category){
          $article= $this->storyUtility->getStoryByCategory($category,$slug,$brand)->first();
          if(!empty($article)){
            $categoriesData[] = [
                'all' => $category->categories . ',' . str_replace(' Spotlight','',$category->categories) . ',' .Str::slug($category->categories),
                'name' =>  $category->categories,
                'shortName' => str_replace(' Spotlight','',$category->categories),
                'slug' =>  $category->slug,
                'description' => $category->description ?? null,
                'image' => env('AWS_S3_URL'). "/category/" . $category->photo ?? null,
            ];
        }
      }
          $data['defaultTab'] = strtolower($categoriesData[0]['slug']);
          $data['categories'] = $categoriesData;
          return $data;
      } 
    public function details(Request $request)
    {
      $slug = $request->query('slug');
      if(!empty($slug)) {
        $category = Category::select('categories','slug', 'description','photo')
                    ->where('slug','=', $slug)
                    ->first();
        if(!empty($category)){
          return response()->json([
            "data" =>  [
              'title' => $category->categories ?? null,
              'description' => $category->description ?? null,
              'slug' => $category->slug ?? null,
              'media' => [
                  'type' => 'image',
                  'url' => env('AWS_S3_URL').'/category/'.$category->photo ?? null, 
                  'path' =>  'category/'.$category->photo
              ]
            ]
          ]);
        }
        else {
          return response()->json(['message' => 'Category not found']);
        }
      } else {
          return response()->json(['message' => 'Please check the endpoint URL']);
      }
    }
}