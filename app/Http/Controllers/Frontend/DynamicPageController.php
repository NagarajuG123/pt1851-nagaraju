<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DynamicPageContents;
use App\Transformers\DynamicTransformer;

class DynamicPageController extends Controller
{
  public function index()
  {
    $series = DynamicPageContents::select(['dynamic_page_contents_title','dynamic_page_contents_url','splash_image'])
            ->where('dynamic_page_contents_featured','!=', 1)
            ->orderBy('dynamic_page_contents_id', 'DESC')
            ->limit(4)
            ->get();
    return response()->json([
        "data" =>  DynamicTransformer::collection($series)
    ]);
  }
}
