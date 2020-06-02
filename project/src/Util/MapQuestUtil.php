<?php
 
namespace App\Util;
 
use App\Entity\User;
use App\Util\MapQuestUtil;
use GuzzleHttp\Client as GuzzleClient;
 
class MapQuestUtil
{
    public function getLatLongByAddress(User $user): ?array
    {
        $client = new GuzzleClient(
          ['base_uri' => $_ENV['MAP_QUEST_API_URL']]
        );
 
        $response = $client->request(
            'GET',
            'address', [
                'query' => [
                    'key' => $_ENV['MAP_QUEST_API_KEY'],
                    'inFormat' => 'kvp',
                    'outFormat' => 'json',
                    'location' => $user->getTown() . ',' . $user->getCounty(),
                    'thumbMaps' => 'false'
                ]
            ]
        );
 
        if ($response->getStatusCode() !== 200) {
            return null;
        }
 
        $body = json_decode($response->getBody()->getContents());
 
        if (!is_array($body) || empty($body)) {
            return null;
        }
 
        if (!array_key_exists('results', $body) || empty($body['results'])) {
            return null;
        }
 
        return $body['results'][0]['locations'][0]['latLng'];
    }
}