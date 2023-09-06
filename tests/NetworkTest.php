<?php

namespace Odin\IP;

use PHPUnit\Framework\TestCase;

class NetworkTest extends TestCase
{
    public function testSummarize()
    {
        $networks = [
            '192.168.0.0/24',
            '192.168.1.0/24',
            '192.168.2.0/24',
            '192.168.3.0/24'
        ];
        $expected_result = IPv4Network::from('192.168.0.0/22');
        
        $this->assertEquals($expected_result, Network::summarize($networks));
    }
}