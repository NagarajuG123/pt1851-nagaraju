<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\YoutubeVideo;
use App\Models\BrandVideos;
use App\Utility\CommonUtility;
use App\Utility\BrandUtility;

use App\Transformers\VideoTransformer;

class VideoController extends Controller
{
    public $commonUtility,$brandUtility;
    public function __construct()
    {
        $this->commonUtility = app(CommonUtility::class);
        $this->brandUtility = app(BrandUtility::class);

    }
    public function index(Request $request){
        $limit = $request->query('limit') ?? 10;
        $slug = $request->query('slug');
        if(!empty($slug)){
            $data = $this->brandUtility->brandExist($slug);
            if($data['status'] == 200) {
                $brandId = $data['data']->id;
                $query = BrandVideos::where('brandId','=',$brandId);
                $brandVideo = null;
                if($query->count() >= 2 ) {
                    $videos = $query->get();
                    foreach($videos as $video) {
                        $id = $this->commonUtility->getVideoId($video);
                        $brandVideo[] = [
                            'id' => $id  ?? null,
                            'title' => $video->title  ?? null,
                            'media' => [
                                'type' => 'video',
                                'url' => 'https://www.youtube.com/embed/'.$id ?? null,
                                'placeholder' => env('YOUTUBE_PLACEHOLDER_URL').$id.env('YOUTUBE_PLACEHOLDER_DEFAULT_IMAGE')?? null,
                                'iframe' => '<iframe itemprop ="video" class="img-responsive info-videos" src="//www.youtube.com/embed/'.$id.'?rel=0&?wmode=transparent&?controls=0&showinfo=0" frameborder="0"  allowfullscreen style="max-width:100%; height:400" height="400" width="55%"></iframe>',
                                'path' => 'brand/videos/'.$video->videoImage ?? null
                            ]
                        ];
                    }
                }
                return ["data" => $brandVideo];
            } else{
                return $data['message'];
            }
        }
        else{
            return response()->json([
                'data' =>  VideoTransformer::collection(YoutubeVideo::paginate($limit))
            ]);
        }
    }
}
