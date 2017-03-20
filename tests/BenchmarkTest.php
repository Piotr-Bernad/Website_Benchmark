<?php 
namespace Tests;

require_once './Benchmark.php';
require './vendor/autoload.php';

/**
 * Unit test of Benchmark
 *
 * @coversDefaultClass Benchmark
 * @author Piotr Bernad 
 */
class BenchmarkTest extends \PHPUnit\Framework\TestCase 
{
    /**
     * @covers ::validateURL
     * @dataProvider url
     */
    public function test_validateURL($url, $expected) 
    {
        $mailServiceMock = $this->getMockBuilder('\Service\MailService')
                ->setMethods(['sendEmailMessage'])
                ->getMock();
        $smsServiceMock = $this->getMockBuilder('\Service\SMSService')
                ->setMethods(['sendSMSMessage'])
                ->getMock();

        $benchmark = new \Benchmark\Benchmark($mailServiceMock, $smsServiceMock);

        try {
            $actual = $benchmark->validateURL($url);
        } catch (\Exception $e) {
            $this->assertEquals($expected, $e->getMessage());
        }
        if (isset($actual)) $this->assertEquals($expected, $actual);

    }

    /**
     * Check if sendEmail and sendSMS properties are set properly
     *
     * @covers ::generateReport
     * @dataProvider time
     */
    public function test_generateReport($time) 
    {
        $benchmarkMock = $this->getMockBuilder('\Benchmark\Benchmark')
                ->disableOriginalConstructor()
                ->setMethods(['websiteLoadingTime', 'getCompetitors'])
                ->getMock();

        $benchmarkMock->expects($this->any())
                ->method('websiteLoadingTime')
                ->with($this->anything())
                ->will($this->onConsecutiveCalls(20, $time, 20, 30));
        
        $benchmarkMock->method('getCompetitors')
                ->will($this->returnValue(['www.example.com', 'example.pl', 'www.example.pl']));
                
        $benchmarkMock->generateReport();

        if ($time <20 && $time >10) {
            $this->assertTrue($benchmarkMock->getSendEmail());
            $this->assertFalse($benchmarkMock->getSendSMS());
        } elseif ($time <= 10) {
            $this->assertTrue($benchmarkMock->getSendEmail());
            $this->assertTrue($benchmarkMock->getSendSMS());
        } else {
            $this->assertFalse($benchmarkMock->getSendEmail());
            $this->assertFalse($benchmarkMock->getSendSMS());
        }
    }

    public function url() 
    {
        return [
            ['www.example.pl', 'http://www.example.pl'], 
            ['example.pl', 'http://example.pl'], 
            ['example.pl-123', 'Wrong website URL provided'],
            ['q.example.pl', 'Wrong website URL provided'],
            ['q.exa$#mple.pl', 'Wrong website URL provided'],
            [
                ['www.example.pl', 'example.pl'], ['http://www.example.pl', 'http://example.pl']
            ],
            [
                ['www.example.pl', 'example.pl', 'www.exam*78ple.com'], 'Wrong competitors URL provided'
            ]
        ];
    }

    public function time() 
    {
        return [
            [20], [15], [10]];
    }
}