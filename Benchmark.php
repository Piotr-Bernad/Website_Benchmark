<?php
namespace Benchmark;

/**
 * Benchmark loading time of the website in comparison to the other websites
 *
 * If the benchmarked website is loaded slower than at least one of the competitors ­send email message
 * to specified email address, if is loaded twice as slow as at least one of the competitors send sms 
 * message alongside the email message. 
 *
 * @author Piotr Bernad
 */
class Benchmark
{
    const COMPETITORS = 'www.apple.com|www.interia.pl|www.gazeta.pl';
    const REPORT_DIR = __DIR__ . '/report';
    const EMAIL_RECIPIENT = 'bernad.p8@gmail.com';
    const SMS_RECIPIENT = '48514418167';
    const SMS_MESSAGE = 'WEBSITE BENCHMARK - report sent by e-mail';
    
    /**
     * @var string
     */
    private $website;
    
    /**
     * @var array
     */
    private $competitors = [];
    
    /**
     * @var string
     */
    private $data;
    
    /**
     * @var MailService
     */
    private $mailService;
    
    /**
     * @var SMSService
     */
    private $smsService;
    
    /**
     * @var boolean
     */
    private $sendEmail = false;
    
    /**
     * @var boolean
     */
    private $sendSMS = false;

    public function __construct(\Service\MailService $mailService, \Service\SMSService $smsService)
    {
        $this->mailService = $mailService;
        $this->smsService = $smsService;
    }
    
    /**
     * Runs the process
     */
    public function run() 
    {
        try {
            $this->setUrls();
            $this->generateReport();
            self::println($this->data);
            $this->logIntoFile($this->data);
            if (isset($this->sendEmail)) $this->mailService->sendEmailMessage(self::EMAIL_RECIPIENT, $this->data);
            if (isset($this->sendSMS)) $this->smsService->sendSMSMessage(self::SMS_RECIPIENT, self::SMS_MESSAGE);
        } catch (\Exception $e) {
            self::println($e->getMessage());
        }
    }
    
    /**
     * Set urls for script execution
     */
    private function setUrls() 
    {
        $urls = getopt("w:c:");

        if (!isset($urls['w'])) {
            throw new \Exception ('Website not provided');
        }
        $this->website = $this->validateURL($urls['w']);
        $competitors = isset($urls['c']) ? $urls['c'] : self::COMPETITORS;
        $competitors = explode('|', $competitors);
        $this->setCompetitors($this->validateURL($competitors));
    }

    /**
     * Validate URL
     *
     * @param string|string[] $url
     *
     * @return string|string[]
     */
    public function validateURL($url) 
    {
        if (is_array($url)) {
            $urls = $url;
            $result = [];
            foreach ($urls as $url) {
                $url = $this->formatURL($url);
                if (!preg_match('#^(?:http(?:s)?:\/\/)?(?:www\.)?(?:[\w-]*)\.\w{2,}$#', $url)) {
                    throw new \Exception('Wrong competitors URL provided');
                }
                $result[] = $url;
            }
        } else {
            $url = $this->formatURL($url);
            if (!preg_match('#^(?:http(?:s)?:\/\/)?(?:www\.)?(?:[\w-]*)\.\w{2,}$#', $url)) {
                throw new \Exception ('Wrong website URL provided');
            }
            $result = $url;
        }

        return $result;
    }

    /**
     * Format URL
     *
     * @param string $url
     *
     * @return string
     */
    private function formatURL($url) 
    {
        if (strpos($url, 'www.') === 0) $url = 'http://'. $url;
        if (strpos($url, 'http') === false) $url = 'http://'. $url;

        return $url;
    }
    
    /**
     * Generate report, set up sendSMS and sendEmail properties if met condition
     */
    public function generateReport() 
    {
        $websiteExecTime = $this->websiteLoadingTime($this->website);
        
        $competitorsExecTime = [];
        foreach ($this->getCompetitors() as $competitor) {
            $competitorsExecTime[$competitor] = $this->websiteLoadingTime($competitor);
        }

        $this->data = 'Test date - ' . date('d-M-Y') . PHP_EOL;
        $this->data .= 'Site being tested - ' . $this->website . ' loaded in ' . $websiteExecTime . ' sec' . PHP_EOL;
        
        $i = 1;
        $msg = 'Benchmarked website is loaded faster than competitors ' . PHP_EOL;
        foreach ($competitorsExecTime as $competitor => $competitorExecTime) {
            $this->data .= 'Competitor ' . $i . ' - ' . $competitor . ' loaded in ' . $competitorExecTime . ' sec' . PHP_EOL;
            $i++;

            if ($websiteExecTime > $competitorExecTime) {
                if (!isset($conditionMsg)) $conditionMsg = 'Benchmarked website is loaded slower than at least one of competitors ' . PHP_EOL;
                $this->setSendEmail();
            }
            
            if ($websiteExecTime >= bcmul($competitorExecTime, 2, 6)) {
                $conditionMsg = 'Benchmarked website is loaded twice as slow as at least one of competitors ' . PHP_EOL;
                $this->setSendSMS();
            }
        }

        $this->data .= isset($conditionMsg) ? PHP_EOL . $conditionMsg : PHP_EOL . $msg;
    }

    /**
     * Log data into a file
     *
     * @param string $text
     */
    private function logIntoFile($text) 
    {
        self::println('Generating a file with report...');
        if (!file_exists(self::REPORT_DIR)) {
            if (!mkdir(self::REPORT_DIR, 0755)) {
                throw new \Exception('Failed to create ' . self::REPORT_DIR);
            }
        }
        file_put_contents(self::REPORT_DIR . '/log.txt', $text . PHP_EOL);

        self::println('Successfully!!!');
    }

    /**
     * Calculate website loading time
     *
     * @param string $url
     *
     * @return float
     */
    protected function websiteLoadingTime($url) 
    {
            $start = microtime(true);
            $homepage = @file_get_contents($url);
            if (!$homepage) throw new \Exception('Wrong URL Provided');
            $total =  microtime(true)-$start;
        
        return $total;
    }

    /**
     * Just print a line
     *
     * @param string $msg
     */
    static public function println($msg)
    {
        print sprintf('%s%s',
            $msg,
            PHP_EOL
        );
    }

    public function setSendEmail()
    {
        $this->sendEmail = true;
    }

    public function getSendEmail()
    {
        return $this->sendEmail;
    }

    public function setSendSMS()
    {
        $this->sendSMS = true;
    }

    public function getSendSMS()
    {
        return $this->sendSMS;
    }

    public function getCompetitors()
    {
        return $this->competitors;
    }

    public function setCompetitors(array $competitors)
    {
        $this->competitors = $competitors;
    }
}