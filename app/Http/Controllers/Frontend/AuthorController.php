<?php

namespace App\Http\Controllers\Frontend;

use yii\helpers\ArrayHelper;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Utility\AuthorUtility;

class AuthorController extends Controller
{
  public $authorUtility;

    public function __construct()
    {
        $this->authorUtility = app(AuthorUtility::class);
    }

    public function details(Request $request){
       $slug = $request->query('slug');
       if(!empty($slug)) {
        $author = $this->authorUtility->fetchBySlug($slug);
        $data = [
          'status' => false,
          'message' => 'Author Not found'
        ];
        if($author) {
          $data =   
          [
              'id' => $author->id ?? null,
              'first_name' => $author->first_name ?? null,
              'last_name' =>  $author->last_name ?? null,
              'slug' => $slug ?? null,
              'designation' =>  $author->author_title ?? null,
              'site_name' =>  $author->company ?? null,
              'about' =>  $author->user_description ?? null,
              'media' => $author->getMediaUrl(),
              'socialMedia' => $author->getSocialMedia()
            ];
          }
          return response()->json($data);
       } else{
          return response()->json([
            'message' => "Invalid API Endpoint"
        ]);
      }
    }
}
