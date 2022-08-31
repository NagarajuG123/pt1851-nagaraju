<?php

namespace App\Http\Controllers\Frontend;
use App\Utility\CommonUtility;
use App\Http\Controllers\Controller;

class PublicationController extends Controller
{
    public $commonUtility;
    public function __construct()
    {
        $this->commonUtility = app(CommonUtility::class);
    }
    public function index()
    {
                        $publication = $this->commonUtility->publication();
                        $socialLinks =
                            [
                                [
                                    'name' => 'Twitter',
                                    'url' => $publication->twitter ?? null,
                                ],
                                [
                                    'name' => 'Facebook',
                                    'url' => $publication->facebook ?? null,
                                ],
                                [
                                    'name' => 'Instagram',
                                    'url' => $publication->instagram ?? null,
                                ],
                                [
                                    'name' => 'LinkedIn',
                                    'url' => $publication->linkedin ?? null,
                                ],
                                [
                                    'name' => 'Youtube',
                                    'url' => $publication->youtube ?? null,
                                ]
                           ];
                    
                        return [
                            'id' => $publication->uniqueId ?? null,
                            'title' => $publication->name ?? null,
                            'url' => $publication->url ?? null,
                            'logo' => env('IMAGE_PROXY_URL'). '/static/'.$publication->logo ?? null,
                            'newsType' => $publication->newstype ?? null,
                            'videoTitle' => $publication->videoTitle ?? null,
                            'sponsorHeading' => $publication->sponsorHeading ?? null,
                            'shortTitle' => $publication->name ?? null,
                            'socialLinks' => $socialLinks ?? null,
                        ];     
    }
}
