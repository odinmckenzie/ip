<?php

use Odin\IP\IPv4Address;
use PHPUnit\Framework\TestCase;
use Odin\IP\IPv4Network;

class IPv4NetworkTest extends TestCase
{
    public function testSimpleObjCreate()
    {
        $net = new IPv4Network('192.168.1.1', 24);
        $this->assertEquals('192.168.1.0', $net->address());
    }

    public function testFrom()
    {
        $net = IPv4Network::from('192.168.1.1/24');
        $this->assertEquals('192.168.1.0', $net->address());
    }

    public function testHostId()
    {
        $net = new IPv4Network('192.168.1.1', 24);
        $this->assertEquals('0.0.0.0', $net->hostId());
    }

    public function testMask()
    {
        $net = new IPv4Network('192.168.1.1', 24);
        $this->assertEquals(24, $net->mask()->prefix());
    }

    public function testSize()
    {
        $net = new IPv4Network('192.168.1.0', 30);
        $this->assertEquals(2, $net->size());
    }

    public function testHosts()
    {
        $net = new IPv4Network('192.168.1.0', 30);

        $expected = ['192.168.1.1', '192.168.1.2'];
        
        $this->assertEquals($expected, $net->hosts());
    }

    public function testBroadcast()
    {
        $net = new IPv4Network('192.168.1.0', 24);
        
        $this->assertEquals(new IPv4Address('192.168.1.255'), $net->broadcast());
    }

    public function testFirstIP()
    {
        $net = new IPv4Network('192.168.1.0', 24);
        
        $this->assertEquals(new IPv4Address('192.168.1.1'), $net->firstIP());
    }

    public function testLastIP()
    {
        $net = new IPv4Network('192.168.1.0', 24);
        
        $this->assertEquals(new IPv4Address('192.168.1.254'), $net->lastIP());
    }

    public function testContains()
    {
        $net = new IPv4Network('192.168.1.0', 30);

        $this->assertTrue($net->contains('192.168.1.1'));
    }
}