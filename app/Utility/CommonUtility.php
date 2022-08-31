<?php

namespace App\Utility;

use App\Models\Publication;
use App\Models\Registration;

class CommonUtility
{
    public function publication()
    {
        return Publication::where(['uniqueId' => env('SITE_ID')])->first();
    }
    public function brand($slug){
        return Registration::where(['brand_url' => $slug])->first();
    }

    public function media($type, $path, $image=null){
        $url = env('AWS_S3_URL') . '/' . $path;
        if($type === 'video'){
            $url = $image;
            if((strpos($url, 'youtube.com') !== false) || (strpos($url, 'youtu.be') !== false)){
                $videoId = $this->getYoutubeId($url);
                $url = 'https://www.youtube.com/embed/'.$videoId;
                $iframe = '<iframe itemprop ="video" class="img-responsive info-videos" src="//www.youtube.com/embed/'.$videoId.'?rel=0&?wmode=transparent&?controls=0&showinfo=0" frameborder="0"  allowfullscreen style="max-width:100%; height:400" height="400" width="55%"></iframe>';
            } else if(strpos($image, 'vimeo') !== false){
                $data = json_decode( file_get_contents( 'https://vimeo.com/api/oembed.json?url=' . $image ) );
                $vimeoId = $data->video_id;
                $url = 'https://player.vimeo.com/video/'.$vimeoId;
            }
        }
        $data = [
            'type' => $type,
            'url' => $url,
            'path' => $path,
        ];
        if ((strpos($image, 'youtube.com') !== false) || (strpos($url, 'youtu.be') !== false)) {
            $data['iframe'] = $iframe;
        }
        return $data;
    }

    public function getYoutubeId($url){
        $parts = parse_url($url);
        if (isset($parts['host'])) {
            $host = $parts['host'];
            if (
                false === strpos($host, 'youtube') &&
                false === strpos($host, 'youtu.be')
            ) {
                return false;
            }
        }
        if (isset($parts['query'])) {
            parse_str($parts['query'], $qs);
            if (isset($qs['v'])) {
                return $qs['v'];
            }
            else if (isset($qs['vi'])) {
                return $qs['vi'];
            }
        }
        if (isset($parts['path'])) {
            $path = explode('/', trim($parts['path'], '/'));
            return $path[count($path) - 1];
        }
        return false;
    }
    public function getVideoId($video) {
        $videoId = explode("?v=", $video->url); 
        if (empty($videoId[1])) {
            $videoId = explode("/v/",  $video->url); 
            $id = explode("&", $videoId[1]); 
        } else {
            $id = $videoId[1];
        }
        return $id;
    }
}   