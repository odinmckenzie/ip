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
    public function testInvalidIPv4AddressInput($ip, $message)
    {
        $this->expectException(InvalidAddressException::class);
        $this->expectExceptionMessage($message);
        new IPv4Address($ip);
    }

    public function invalidIPv4AddressProvider()
    {
        return [
            ['192.168.1.256', "'192.168.1.256' is in an unexpected format"],
            ['192.168.1.1.1', "'192.168.1.1.1' is in an unexpected format"],
            ['192.168.1.', "'192.168.1.' is in an unexpected format"],
            ['192.168', "'192.168' is in an unexpected format"],

            ['192.168.1.1/24', "Unexpected '/' found in '192.168.1.1/24'"],
            ['192.168.1.1/255.255.255.0', "Unexpected '/' found in '192.168.1.1/255.255.255.0'"],
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

    public function testIPv4AddressAdd()
    {
        $ip = new IPv4Address('192.168.1.1');
        $next_ip = $ip->add(1);

        $this->assertEquals('192.168.1.2', $next_ip->address());
    }

    public function testIPv4AddressAddUpperLimit()
    {
        $this->expectException(InvalidAddressException::class);
        $this->expectExceptionMessage("'255.255.255.250' + 6 is greater than '255.255.255.255'");

        $ip = new IPv4Address('255.255.255.250');
        $ip->add(6);
    }

    public function testIPv4AddressAddLowerLimit()
    {
        $this->expectException(InvalidAddressException::class);
        $this->expectExceptionMessage("'0.0.0.0' + -2 is less than '0.0.0.0'");

        $ip = new IPv4Address('0.0.0.0');
        $ip->add(-2);
    }
}