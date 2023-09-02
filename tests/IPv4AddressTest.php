<?php

use PHPUnit\Framework\TestCase;
use Odin\IP\IPv4Address;
use Odin\IP\InvalidAddressException;

class IPv4AddressTest extends TestCase
{
    public function testSimpleObjCreate()
    {
        $ip = new IPv4Address('192.168.1.1');
        $this->assertEquals('192.168.1.1', $ip->address());

        // has space after address
        $ip = new IPv4Address('192.168.1.2 ');
        $this->assertEquals('192.168.1.2', $ip->address());
    }

    /**
     * @dataProvider invalidIPv4AddressProvider
     */
    public function testInvalidIPv4AddressInput($ip, $code)
    {
        $this->expectException(InvalidAddressException::class);
        $this->expectExceptionCode($code);
        new IPv4Address($ip);
    }

    public function invalidIPv4AddressProvider()
    {
        return [
            ['192.168.1.256', 0],
            ['192.168.1.1.1', 0],
            ['192.168.1.', 0],
            ['192.168', 0],
            ['192.168.1.1/24', 1],
            ['192.168.1.1/255.255.255.0', 1],
        ];
    }

    public function testIPv4AddressObjComparison()
    {
        $this->assertTrue(new IPv4Address('192.168.1.1') == new IPv4Address('192.168.1.1'));
        $this->assertTrue(new IPv4Address('192.168.1.2') != new IPv4Address('192.168.1.1'));

        $this->assertTrue(new IPv4Address('192.168.1.2') > new IPv4Address('192.168.1.1'));
        $this->assertTrue(new IPv4Address('192.168.1.1') < new IPv4Address('192.168.1.3'));

        $this->assertEquals(-1, new IPv4Address('192.168.1.1') <=> new IPv4Address('192.168.1.3'));
        $this->assertEquals(0, new IPv4Address('192.168.1.1') <=> new IPv4Address('192.168.1.1'));
        $this->assertEquals(1, new IPv4Address('192.168.1.2') <=> new IPv4Address('192.168.1.1'));
    }
}