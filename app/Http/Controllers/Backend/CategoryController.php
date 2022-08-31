<?php

namespace App\Http\Controllers\Backend;
use DB;

use App\Models\Category\Base;
use App\Models\Category\Keywords;

use App\Utility\CommonUtility;
use App\Utility\CategoryUtility;

use App\Http\Controllers\Controller;


use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
{
  public   $categoryUtility;
  public   $commonUtility;
  public function __construct()
  {
      $this->commonUtility = app(CommonUtility::class);
      $this->categoryUtility = app(CategoryUtility::class);

  }
  public function index(Request $request)
  {
    $limit = $request->query('limit') ?? 5;
    $searchQuery = $request->query('q'); 
    $categories = Base::select('id', 'name','slug', 'description','image_path','seo_title','seo_description')
                    ->whereNotNull('sort_id')
                    ->whereNull('deleted_at');
    if($searchQuery) {
        $categories = $categories->where(DB::raw("name"), 'like', '%' . $searchQuery . '%')
        ->orWhere(DB::raw("description"), 'like', '%' . $searchQuery . '%');
    }
    $categories =  $categories->orderBy('sort_id', 'ASC')->paginate($limit);
    foreach($categories as $category)
      {    
        $data[] = [
              'id' => $category->id ?? null,
              'name' => $category->name ?? null,
              'slug' => $category->slug ?? null,
              'description' =>  $category->description ?? null,
              'meta_title' =>  $category->seo_title ?? null,
              'meta_description' =>  $category->seo_description ?? null,
              'keywords' => $category->keywords->isNotEmpty() ? $this->categoryUtility->formatKeywords($category->keywords) : null,
              'media' =>$this->commonUtility->media('image',$category->image_path) ?? null,
         ];
    }
    return response()->json([ 
        'hasMore' => $categories->hasMorePages(),
        'data' => $data ?? null
     ]);
  }
  
  public function showOneCategory($id)
  {
    $category =  $this->categoryUtility->fetchById($id);
    if(!empty($category))
    {
        return [
            'id' => $category->id ?? null,
            'name' => $category->name ?? null,
            'slug' =>  $category->slug ?? null,
            'description' =>  $category->description ?? null,
            'meta_title' =>  $category->seo_title ?? null,
            'meta_description' =>  $category->seo_description ?? null,
            'keywords' =>$category->keywords->isNotEmpty() ? $this->categoryUtility->formatKeywords($category->keywords) : null,
            'media' =>$this->commonUtility->media('image',$category->image_path) ?? null,

        ];
    }
    else{
        return response()->json([
          "status" => 404,
          'message' => "Category not found"
      ]);
    }
  }

  public function create(Request $request)
  {
    $validator = Validator::make($request->json()->all(),[
        'name' => 'required',
        'slug' => 'required|unique:category,slug',
        'description' => 'required',
        'image_path' => 'required',
        'seo_title' => 'required',
        'seo_description' => 'required'
    ]);
    if ($validator->passes($request)) {
        $lastCategory = Base::select('sort_id', 'id')->orderBy('sort_id', "DESC")->first();
        $categories = $request->all();
        $categories['sort_id'] = !empty($lastCategory->sort_id) ? $lastCategory->sort_id + 1 : 1;
        $categories['created_by'] = Auth::user()->id;
        $categories['updated_by'] = Auth::user()->id;
        $category = Base::create($categories);
        if (!empty($request->keywords)) {
            foreach($request->keywords as $keyword) {
                $categoryKeywords = Keywords::create(['name' => $keyword ?? null, 'category_id' => $category->id ?? null]);
            }
        }
        return response()->json([
            "success" => true,
            "data" => [
                "id" => $category->id,
                "message" => "Category created successfully"
            ]
        ]);
    } else {
        return response()->json($validator->errors(), 400);
      }
  }
  
  public function update($id, Request $request) 
  {
      $validator = Validator::make($request->json()->all(), [
          'name' => 'required',
          'slug' => 'required|unique:category,slug,'.$id,
          'description' => 'required',
          'image_path' => 'required',
          'seo_title' => 'required',
          'seo_description' => 'required',
      ]);
      if ($validator->passes($request)) {
          $category = Base::findOrFail($id);
          $category->update($request->all());
          $categoryKeywords = Keywords::where('category_id', $id)->delete();
          if (!empty($request->keywords)) {
              foreach($request->keywords as $keyword) {
                  $categoryKeywords = Keywords::create(['name' => $keyword ?? null, 'category_id' => $id ?? null]);
              };
          }
          return response()->json([
            "success" => true,
            "data" => [
                "id" => $id,
                "message" => "Category updated successfully"
            ]   
          ]);
      } else {
          return response()->json($validator->errors(), 400);
      }
  }
  public function delete($id)
  {
    $category = Base::findOrFail($id);
    if(empty($category->deleted_at)){
        $category->update(['deleted_at' => (new \DateTime())->format('Y-m-d H:i:s')]);
        return response()->json([
            'success' => true,
            'message' => "Category deleted successfully"
            ]);
    }
    else{
        return response()->json([
            "success" => true,
            "message" => "Category does not exist"
        ]);
    }
  }
}
