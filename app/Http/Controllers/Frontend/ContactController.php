<?php

namespace App\Http\Controllers\Frontend;


use App\Http\Controllers\Controller;
use App\Models\StaticPage;

use App\Utility\CommonUtility;


class ContactController extends Controller
{
  public function __construct()
  {
    $this->commonUtility = app(CommonUtility::class);
  }
  public function index()
  {
      $model = StaticPage::select(['id','title','description','slug','seo_title','media_id','image_path'])
              ->where('id' , StaticPage::CONTACT_1851_EDITORIAL)
              ->first();
      return [
        'title' => $model->title ?? null,
        'short_description' => $model->description ?? null,
        'slug' =>  $model->slug ?? null,
        'seo_title' =>  $model->seo_title ?? null,
        'media' => $this->commonUtility->media($model->media->name,$model->image_path) ?? null,
      ];
  }
}
