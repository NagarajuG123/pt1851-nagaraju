<?php

namespace App\Http\Controllers\Frontend;


use App\Http\Controllers\Controller;

use App\Models\AboutUs;

use App\Utility\CommonUtility;

class AboutController extends Controller
{
  public   $commonUtility;
  public function __construct()
  {
      $this->commonUtility = app(CommonUtility::class);
  }
   public function index()
   {
      $model = AboutUs::select('title', 'description','video_url', 'placeholder_path','iframe','content_title','content_description',
                              'publication_title','publication_description','publication_image_path','content_marketing_title',
                              'content_marketing_description','content_marketing_image_path','demo_description','demo_image_path')
                      ->first();
      $contents = [
        [
            'title' => $model->content_title,
            'short_description' => $model->content_description,
            'media' => null,
        ],
        [
            'title' => $model->publication_title,
            'short_description' => $model->publication_description,
            'media' =>  $this->commonUtility->media('image', $model->publication_image_path),
        ],
        [
            'title' => $model->content_marketing_title,
            'short_description' => $model->content_marketing_description,
            'media' =>  $this->commonUtility->media('image', $model->content_marketing_image_path),
        ]
      ];
      return [
        "title" => $model->title ?? null,
        "description" => $model->description ?? null,
        'media' => [
            'type' => 'video',
            'url' => $model->video_url,
            'placeholder' => env('AWS_S3_URL').'/'.$model->placeholder_path,
            'iframe' => $model->iframe
        ],
        "contents" => $contents,
        "demo" => [
            'description' => $model->demo_description,
            'media' =>  $this->commonUtility->media('image', $model->demo_image_path),
        ]
      ]; 
   }
}
