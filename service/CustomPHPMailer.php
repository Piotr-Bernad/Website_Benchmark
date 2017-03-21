<?php
namespace Service;

require_once 'MailServiceInterface.php';
require_once './Benchmark.php';
require_once './vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
require './vendor/autoload.php';

/**
 *
 * @author Piotr Bernad
 */
class CustomPHPMailer extends \PHPMailerOAuth implements MailService 
{
    const SUBJECT = 'Benchmark loading time report';
    const SENDER = 'benchmark.bern@gmail';

    public function __construct() {
        $this->isSMTP();
        $this->SMTPDebug = 0;
        $this->Host = "smtp.gmail.com";                       
        $this->Port = 587;
        $this->SMTPSecure = 'tls';                            
        $this->SMTPAuth = true;
        $this->AuthType = 'XOAUTH2';
        $this->oauthUserEmail = "benchmark.bern@gmail.com";
        $this->oauthClientId = "362007428153-6mj9e0vsb799n9bvoq1rrmntbf3tb5bo.apps.googleusercontent.com";
        $this->oauthClientSecret = "k3Qbz-y2NU4xN5rhKunX3Nki";
        $this->oauthRefreshToken = "1/63_b99rl7mHgrcWwtzL8pvjIztjjsMcwjRf9bp0rQTs";
    }
    
    /**
    * Send email to specified address
    */
    public function sendEmailMessage($recipient, $msg) {

        $this->setFrom(self::SENDER);
        $this->addAddress($recipient);
        $this->Subject = self::SUBJECT;
        $this->Body    = $msg;
        $this->AddAttachment(\Benchmark\Benchmark::REPORT_DIR . "/log.txt");     

        try {
            \Benchmark\Benchmark::println('Sending an email message...');
            $this->send();
            \Benchmark\Benchmark::println('Successfully!!!');
        } catch (\Exception $e){
            throw new \Exception('Failed!!! - ' . $e->getMessage());
        }
    } 
}