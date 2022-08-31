<?php

namespace App\Utility;

use DB;
use App\Utility\RegistrationUtility;
use Illuminate\Support\Str;
use App\Models\Registration;
use App\Models\UserCategory;

class AuthorUtility
{
    public function fetchBySlug($slug) {
        $names = explode('-', $slug);
        $data = null;
        if(count($names) == 2) {
            list($first_name, $last_name) = explode('-', $slug); 
            $data =  Registration::select(['id','first_name','last_name','author_title','company','user_description','photo','facebook_link','twitter_link','instagram_link','linkedin_link', 'is_deleted'])
            ->active()
            ->where(DB::raw("REPLACE(first_name, ' ', '')"), $first_name)
            ->where(DB::raw("REPLACE(last_name, ' ', '')"),  $last_name)
            ->first();
        } 
        return $data;
    }
    public static function getDetails($article){
        $registration = new RegistrationUtility();
        $authorId = $article->user_author ?? $article->author_id;
        if (empty($authorId)) {
            $authorId = $article->user_id;
        }
        $author = $registration->getDetails($authorId);
        if(!empty($author)){
            $name = str_replace(' ', '',$author->first_name).' '.str_replace(' ', '',$author->last_name);
            $siteAuthorMedia = [
                'type' => 'image',
                'url' => env('AWS_S3_URL') . '/author/'.$author->photo,
                'path' => '/author/'.$author->photo,
            ];
            if (empty($author->photo)) {
                $siteAuthorMedia = [
                'type' => 'image',
                'url' => env('AWS_S3_URL') . '/author/no-image.png',
                'path' => '/author/no-image.png',
            ];
            }
            $data = [
                'id' => $author->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'designation' => $author->author_title,
                'media' => $siteAuthorMedia,
            ];
        }
        
        return $data ?? null;
    }
}