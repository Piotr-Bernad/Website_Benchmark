<?php
namespace Service;

require_once 'SMSServiceInterface.php';

/**
 *
 * @author Piotr Bernad
 */

class SMSApiService implements SMSService 
{
    const TOKEN = '15AfaEHbpRtwSdRAfbwQsNKk4N9wJQr5sDgkOipk';
    const URL = 'https://api.smsapi.pl/sms.do';
    const BACKUP_URL = 'https://api2.smsapi.pl/sms.do';
    const SENDER = 'Info';

    /**
    * Send sms message to specified address
    *
    */
    public function sendSMSMessage($to, $message) {
        
        $url = self::URL;
        $params = [
            'to' => $to,
            'from' => self::SENDER,
            'message' => $message,
        ];

        \Benchmark\Benchmark::println('Sending sms message...');

        $result = $this->createCurl($params, $url);

        if($result['http_status'] != 200) {
            \Benchmark\Benchmark::println('Failed!!! - (http_status ' . $result['http_status'] . ') Retrying with backup URL...');
            $url = self::BACKUP_URL;
            $result = $this->createCurl($params, $url);
        }

        if($result['http_status'] == 200 && strpos($result['content'], 'ERROR') === false){
            \Benchmark\Benchmark::println('Successfully!!!');
        } else {
            \Benchmark\Benchmark::println('Failed!!! - (http_status ' . $result['http_status'] . ') '. $result['content']);
        }
    }
    
    /**
    * Send sms message to specified address
    *
    */
    public function createCurl($params, $url) {

        $c = curl_init();
        curl_setopt( $c, CURLOPT_URL, $url );
        curl_setopt( $c, CURLOPT_POST, true );
        curl_setopt( $c, CURLOPT_POSTFIELDS, $params );
        curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $c, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer " . self::TOKEN 
        ));

        $content = curl_exec( $c );
        $http_status = curl_getinfo($c, CURLINFO_HTTP_CODE);

        curl_close( $c );    
        
        return $result = [
            'content' => $content,
            'http_status' => $http_status
        ];
    }

}