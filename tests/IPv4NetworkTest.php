<?php

use PHPUnit\Framework\TestCase;
use Odin\IP\IPv4Network;

class IPv4NetworkTest extends TestCase
{
    public function testSimpleObjCreate()
    {
        $net = new IPv4Network('192.168.1.1', 24);
        $this->assertEquals('192.168.1.0', $net->address());
    }

    public function testHostId()
    {
        $net = new IPv4Network('192.168.1.1', 24);
        $this->assertEquals('0.0.0.0', $net->hostId());
    }
}