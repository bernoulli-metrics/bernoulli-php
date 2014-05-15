<?php

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream;


class BernoulliTest extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNullClientId() {
        $client = new Bernoulli\Client();

        $client->GetExperiments(['signup'], 's59', null, null);
    }

    public function testGetExperimentsHandlesError() {
        $httpClient = self::getHttpClient([
            'success' => 'error',
            'message' => 'Invalid Client Id',
        ]);

        $bernoulliClient = $this->getMockClass('Bernoulli\Client', ['GetHttpClient']);
        $bernoulliClient::staticExpects($this->any())
                        ->method('GetHttpClient')
                        ->will($this->returnValue($httpClient));

        $hit = false;
        try {
            $response = $bernoulliClient::GetExperiments(['signup'], 's60', ['a'], '12345678');
        } catch (Exception $ex) {
            $this->assertEquals('Invalid Client Id', $ex->getMessage());
            $hit = true;
        }

        $this->assertTrue($hit);
    }

    public function testGetExperimentsHandlesSuccess() {
        $httpClient = self::getHttpClient([
            'status' => 'ok',
            'value' => [
                [
                    'userId' => 's60',
                    'variant' => 'trial'
                ]
            ],
        ]);

        $bernoulliClient = $this->getMockClass('Bernoulli\Client', ['GetHttpClient']);
        $bernoulliClient::staticExpects($this->any())
            ->method('GetHttpClient')
            ->will($this->returnValue($httpClient));

        $response = $bernoulliClient::GetExperiments(['signup'], 's60', ['a'], '12345678');
        $this->assertEquals(1, count($response));
        $this->assertEquals('s60', $response[0]['userId']);
        $this->assertEquals('trial', $response[0]['variant']);
    }

    public function testGoalAttainedSuccess() {
        $httpClient = self::getHttpClient([
            'status' => 'ok',
            'value' => [
                'success' => True,
            ],
        ]);

        $bernoulliClient = $this->getMockClass('Bernoulli\Client', ['GetHttpClient']);
        $bernoulliClient::staticExpects($this->any())
            ->method('GetHttpClient')
            ->will($this->returnValue($httpClient));

        $response = $bernoulliClient::GoalAttained('signup', 's60', '12345');
        $this->assertTrue($response);
    }

    private function getHttpClient($response) {
        $httpClient = new Client();

        $mock = new Mock([
            new Response(200, [
                    'Content-Type' => 'application/json',
                ],
                GuzzleHttp\Stream\create(json_encode($response)))
        ]);

        $httpClient->getEmitter()->attach($mock);

        return $httpClient;
    }
}
 