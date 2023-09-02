<?php

use PHPUnit\Framework\TestCase;
use Odin\IP\IPv4Address;

class IPv4AddressTest extends TestCase
{
    public function testSimpleObjCreate()
    {
        $ip = new IPv4Address('192.168.1.1');

        $this->assertEquals('192.168.1.1', $ip->address());
    }
}