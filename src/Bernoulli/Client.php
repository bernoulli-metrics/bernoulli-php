<?php
namespace Bernoulli;

use GuzzleHttp;

class Client {
    const URL = "https://bernoulli.herokuapp.com/client/api/experiments/";

    static public function GetExperiments($experimentIds, $userId, $userData, $clientId)
    {
        $clientId = static::GetClientId($clientId);

        if (is_array($experimentIds)) {
            $experimentIds = join(',', $experimentIds);
        }

        $queryString = [
            'clientId' => $clientId,
            'experimentIds' => $experimentIds,
            'userId' => $userId,
        ];

        if ($userData != null) {
            $queryString = array_merge($queryString, $userData);
        }

        $httpClient = static::GetHttpClient();
        $response = $httpClient->get(self::URL, [
            'query' => $queryString,
        ]);

        if ($response->getStatusCode() != "200") {
            throw new \Exception("Unable to get experiments");
        }

        $data = $response->json();
        if ($data['status'] != 'ok') {
            throw new \Exception($data['message']);
        }

        return $data['value'];
    }

    static public function GoalAttained($experimentId, $userId, $clientId)
    {
        $clientId = static::GetClientId($clientId);

        $queryString = [
            'clientId' => $clientId,
            'experimentId' => $experimentId,
            'userId' => $userId,
        ];

        $httpClient = static::GetHttpClient();

        $response = $httpClient->post(self::URL, [
            'query' => $queryString,

        ]);

        if ($response->getStatusCode() != 200) {
            throw new \Exception("Unable to record goal");
        }

        $data = $response->json();
        if ($data['status'] != 'ok') {
            throw new \Exception($data['message']);
        }

        return $data['value']['success'];
    }

    static private function GetClientId($clientId) {
        if ($clientId == null) {
            $clientId = getenv("BERNOULLI_CLIENT_ID");

            if ($clientId == null){
                throw new \InvalidArgumentException("Invalid clientId");
            }
        }

        return $clientId;
    }

    static public function GetHttpClient() {
        return new GuzzleHttp\Client();
    }
}