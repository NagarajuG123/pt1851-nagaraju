<?php

namespace App\Utility;

use App\Models\Category;
use App\Models\Category\Base;
use App\Models\Category\Keywords;


class CategoryUtility
{
    public function getDetails($slug)
    {
        return Category::where(['slug' => $slug])->first();
    }
    public function fetchById($id)
    {
        return Base::where(['id' => $id])->first();
    }
    public function formatKeywords($keywords)
    {
        foreach($keywords as $keyword){
         $categoryKeywords[] = implode( ",", (array)$keyword->name);
        }
        return $categoryKeywords;
    }
    public function details($article)
    {
        $data = null;
        if (!empty($article->category)) {
            if($article->category->id == Category::CATEGORY_NA) {
                $data = [
                    'id' => '',
                    'name' => '',
                    'slug' => '',
                ];
            } else {
                $data = [
                    'id' => $article->category->id,
                    'name' => $article->category->categories,
                    'slug' => $article->category->slug,
                ];
            }
        }

        return $data;
    }
    public function allCategories()
    {
        return Category::select(['id', 'categories', 'photo', 'description','slug'])
        ->orderBy('sortId', 'ASC')
        ->whereNotNull('sortId')
        ->get();
    }
}   
