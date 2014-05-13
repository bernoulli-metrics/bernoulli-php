<?php
namespace Bernoulli;

use GuzzleHttp;

class Bernoulli {
    const URL = "https://bernoulli.herokuapp.com/client/api/experiments/";

    static public function GetExperiments($experimentIds, $userId, $userData, $clientId)
    {
        $clientId = self::GetClientId($clientId);

        $queryString = [
            'clientId' => $clientId,
            'experimentIds' => join(',', $experimentIds),
            'userId' => $userId,
        ];

        if ($userData != null) {
            $queryString = array_combine($queryString, $userData);
        }

        $httpClient = new GuzzleHttp\Client();
        $response = $httpClient->get(self::URL, [
            'query' => $queryString,
        ]);

        if ($response->getStatusCode() != 200) {
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
        $clientId = self::GetClientId($clientId);

        $queryString = [
            'clientId' => $clientId,
            'experimentId' => $experimentId,
            'userId' => $userId,
        ];

        $httpClient = new GuzzleHttp\Client();
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

        return $data['success'];
    }

    static private function GetClientId($clientId) {
        if ($clientId == null || $clientId == "") {
            $clientId = getenv("BERNOULLI_CLIENT_ID");

            if ($clientId == null || $clientId == "" ){
                throw new \Exception("Invalid $clientId");
            }
        }

        return $clientId;
    }
}