<?php

namespace App\Utility;

use Illuminate\Support\Str;

class MetaUtility
{
    public function details($seoTitle, $seoDescription, $seoKeyword, $author, $robots, $index, $ogTitle, $ogDescription, $siteName, $ogUrl = null, $media = null,$ogImage=null,$twitterImage=null){
        $data = [
            'seo' => [
                'title' => $seoTitle ?? null,
                'description' => $seoDescription ?? null,
                'keywords' => $seoKeyword,
                'author' => $author,
                'robots' => $robots,
                'indexable' => $index,
                'referrer' => 'no-referrer-when-downgrade',
            ],
            'og' => [
                'title' => $ogTitle,
                'type' => 'article',
                'description' => $ogDescription,
                'url' => $ogUrl,
                'image' => $ogImage ?? null,
                'site_name' => $siteName,
            ],
            'twitter' => [
                'card' => 'summary',
                'title' => $ogTitle,
                'description' => $ogDescription,
                'url' => $ogUrl,
                'image' => $twitterImage ?? null,
            ],
            'fb-app-id' => env('FB_ID'),
        ];
        if($media != null){
            $data['og']['media'] = $data['twitter']['media'] = $media;
        }
        return $data;
    }
}