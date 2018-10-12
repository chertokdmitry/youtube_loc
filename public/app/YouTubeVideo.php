<?php

namespace App;

use Google_Client;
use Google_Service_YouTube;

class YouTubeVideo
{
    public $id; 
    private $youtube;

    public function __construct()
    {
        $client = new Google_Client();
        $client->setDeveloperKey('AIzaSyA_3i0GohYnjCPeFCcEr1LZCccu3aSoguk');
        $this->youtube = new Google_Service_YouTube($client);
    }
    
    public function getChanels(string $publishedAfter)
    {
         $videos = $this->youtube->search->listSearch('snippet',
            array(
                'publishedAfter' => $publishedAfter,
                'maxResults' => '39',
                'type'=> 'channel'
            ));
     
        $videoData = $this->filterData($videos['items']);
        return $videoData;
    }
    
    public function getVideoGallery(string $channelId)
    {
        $videos = $this->youtube->search->listSearch('snippet',
            array(
                'channelId' => $channelId,
                'maxResults' => '18'
            ));
        
        $videoData = $this->filterData($videos['items'], true);
        return $videoData;
    }
    
    public function getVideo(string $id)
    {
         $video = $this->youtube->videos->listVideos('snippet, statistics, contentDetails', [
            'id' => $id
        ]);
         
        $videoData = $this->filterData($video['items'], false, true);
        return $videoData;
    }
    
    public function filterData($videoItems, $getVideo = false, $getInfo = false)
    {
        $videoData = [];
        for ($i=0; $i<sizeof($videoItems); $i++)
        {

            $data = get_object_vars($videoItems[$i]);
            $dataSnippet = get_object_vars($data['snippet']);
            
            $dataThubnail = get_object_vars($dataSnippet['thumbnails']);

            if($getInfo) {
                $dataThubnailMedium = get_object_vars($dataThubnail['high']);
            } else {
                $dataThubnailMedium = get_object_vars($dataThubnail['medium']);
            }
            
            if($getVideo){
                $dataId = get_object_vars($data['id']);
                $id = $dataId['videoId'];
            } else {
                $id = $dataSnippet['channelId'];
            }
            
            if($getInfo) {
                
                $dataStatistics = get_object_vars($data['statistics']);
                $dataContentDetails = get_object_vars($data['contentDetails']);
                
                $videoData[] = ['id' => $id,
                        'title' => $dataSnippet['title'],
                        'publishedAt' => $dataSnippet['publishedAt'],
                        'thumbnail' => $dataThubnailMedium['url'],
                        'like' => $dataStatistics['likeCount'],
                        'dislike' => $dataStatistics['dislikeCount'],
                        'view' => $dataStatistics['viewCount'],
                        'duration' => $dataContentDetails['duration'],
                        'dimension' => $dataContentDetails['dimension']];
            } else {               
                $videoData[] = ['id' => $id,
                        'title' => $dataSnippet['title'],
                        'publishedAt' => $dataSnippet['publishedAt'],
                        'thumbnail' => $dataThubnailMedium['url']];
            }
        }
        return $videoData;
    }
}
