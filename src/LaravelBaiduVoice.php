<?php

namespace Dmxl\LaravelBaiduVoice;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LaravelBaiduVoice
{
    /**
     * Constant URLs.
     *
     * @var string
     * @var string
     */
    const QUERY_URL = 'http://vop.baidu.com/server_api';
    const BASE_AUTH_URL = 'https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials';

    /**
     * Configurations.
     *
     * @var array
     */
    private $config;

    /**
     * Baidu Voice API access token.
     *
     * @var string
     */
    private $token;

    /**
     * Private instance of Logger that handles all logs.
     *
     * @var Logger
     */
    private $logger;

    /**
     * Application constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->token = $this->getToken();
        $this->initLogger();
    }

    /**
     * Get the access token.
     *
     * @return string
     */
    private function getToken()
    {
        $ch = curl_init();
        $authUrl = self::BASE_AUTH_URL."&client_id=".$this->config['api_key']."&client_secret=".$this->config['secret'];

        curl_setopt($ch, CURLOPT_URL, $authUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $response = curl_exec($ch);

        if(curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);

        $response = json_decode($response, true);

        return $response['access_token'];
    }

    private function initLogger()
    {
        $this->logger = new Logger('baiduvoice');
        $this->logger->pushHandler(new StreamHandler(config('baiduvoice.log.file'), config('baiduvoice.log.level')));
    }

    /**
     * Voice recognition using the API.
     *
     * @param string $fileDir
     * @param string $format
     * @param string $lan
     * @return string
     */
    public function recognize($fileDir, $format = 'amr', $lan = 'en'){
        $audio = file_get_contents($fileDir);
        $base_data = base64_encode($audio);

        $array = array(
            "format" => $format,
            "rate" => 8000,
            "channel" => 1,
            "lan" => $lan,
            "token" => $this->token,
            "cuid"=> $this->config['cuid'],
            //"url" => "http://www.xxx.com/sample.pcm",
            //"callback" => "http://www.xxx.com/audio/callback",
            "len" => filesize($fileDir),
            "speech" => $base_data,
        );
        $json_array = json_encode($array);
        $content_len = "Content-Length: ".strlen($json_array);
        $header = array ($content_len, 'Content-Type: application/json; charset=utf-8');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::QUERY_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_array);

        $response = curl_exec($ch);

        if(curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);

        $response = json_decode($response, true);
        $this->logger->debug('Recognized result:', $response);

        return $response;
    }
}