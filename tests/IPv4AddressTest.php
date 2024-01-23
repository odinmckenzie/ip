<?php

use PHPUnit\Framework\TestCase;
use Odin\IP\IPv4Address;
use Odin\IP\IPv4Mask;
use Odin\IP\InvalidAddressException;

class IPv4AddressTest extends TestCase
{
    /**
     * @dataProvider simpleObjCreateProvider
     * @return void
     */
    public function testSimpleObjCreate($ip_input, $prefix)
    {
        $ip = new IPv4Address($ip_input);
        $this->assertEquals($ip_input, $ip->address());
        $this->assertEquals($prefix, $ip->mask()->prefix());

        $ip = new IPv4Address($ip_input, $prefix);
        $this->assertEquals($ip_input, $ip->address());
        $this->assertEquals($prefix, $ip->mask()->prefix());

        // has space after address
        $ip = new IPv4Address($ip_input . ' ', $prefix);
        $this->assertEquals($ip_input, $ip->address());
        $this->assertEquals($prefix, $ip->mask()->prefix());
    }

    public function simpleObjCreateProvider()
    {
        return [
            ['10.1.1.1', 8],
            ['172.16.1.1', 16],
            ['192.168.1.1', 24],
            ['224.1.1.1', 32],
            ['250.1.1.1', 32],
        ];
    }

    public function testFrom()
    {
        $ip = IPv4Address::from('192.168.1.2/24 ');
        $this->assertEquals('192.168.1.2', $ip->address());
        $this->assertEquals(24, $ip->mask()->prefix());
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
            ['192.168.1.256', "'192.168.1.256' is in an unexpected format."],
            ['192.168.1.1.1', "'192.168.1.1.1' is in an unexpected format."],
            ['192.168.1.', "'192.168.1.' is in an unexpected format."],
            ['192.168', "'192.168' is in an unexpected format."],
            
            ['192.168.1.1/24', "Unexpected '/' found in '192.168.1.1/24'. Use the from() static method instead."],
            ['192.168.1.1/255.255.255.0', "Unexpected '/' found in '192.168.1.1/255.255.255.0'. Use the from() static method instead."],
        ];
    }

    public function testToString()
    {
        $ip = new IPv4Address('192.168.1.2 ');
        $ip_str = (string) $ip;

        $this->assertEquals('192.168.1.2/24', $ip_str);
    }

    public function testNetwork()
    {
        $ip = new IPv4Address('192.168.1.2', 24);
        $net = $ip->network();

        $this->assertEquals('192.168.1.0', $net->address());
    }

    public function testHostId()
    {
        $ip = new IPv4Address('192.168.1.2', 24);
        $host_id = $ip->hostId();

        $this->assertEquals('0.0.0.2', $host_id);
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

    public function testIPv4AddressSubtract()
    {
        $ip = new IPv4Address('192.168.1.5');
        $next_ip = $ip->subtract(1);

        $this->assertEquals('192.168.1.4', $next_ip->address());
    }

    public function testIPv4AddressSubtractLowerLimit()
    {
        $this->expectException(InvalidAddressException::class);
        $this->expectExceptionMessage("'0.0.0.0' - 2 is less than '0.0.0.0'");

        $ip = new IPv4Address('0.0.0.0');
        $ip->subtract(2);
    }

    public function testIPv4AddressSubtractUpperLimit()
    {
        $this->expectException(InvalidAddressException::class);
        $this->expectExceptionMessage("'255.255.255.250' - -6 is greater than '255.255.255.255'");

        $ip = new IPv4Address('255.255.255.250');
        $ip->subtract(-6);
    }

    public function testIsUnspecifiedAddress()
    {
        $ip = new IPv4Address('0.0.0.0');
        $this->assertTrue($ip->isUnspecified());
    }

    /**
     * @dataProvider toBinaryProvider
     */
    public function testToBinary($address, $expected_binary)
    {
        $ip = new IPv4Address($address);
        $this->assertEquals($expected_binary, $ip->toBinary());
    }

    public function toBinaryProvider()
    {
        return [
            ['10.1.1.1', '00001010000000010000000100000001'],
            ['192.168.0.1', '11000000101010000000000000000001'],
            ['255.255.255.0', '11111111111111111111111100000000'],
            ['0.0.0.0', '00000000000000000000000000000000'],
            ['127.0.0.1', '01111111000000000000000000000001'],
            ['255.255.255.255', '11111111111111111111111111111111'],
        ];
    }

    /**
     * @dataProvider toFormattedBinaryProvider
     */
    public function testToFormattedBinary($address, $netmask, $gap, $expected_binary)
    {
        $ip = new IPv4Address($address, $netmask);
        $this->assertEquals($expected_binary, $ip->toFormattedBinary($gap));
    }

    public function toFormattedBinaryProvider()
    {
        return [
            ['10.1.1.1', '/8', ' ', '00001010. 00000001.00000001.00000001'],
            ['192.168.0.1', 24, null, '11000000.10101000.00000000.00000001'],
            ['255.255.255.0', new IPv4Mask(24), ' ', '11111111.11111111.11111111. 00000000'],
        ];
    }

    public function testIsLoopback()
    {
        $loopback = new IPv4Address('127.0.0.1');
        $this->assertTrue($loopback->isLoopback());

        $private = new IPv4Address('192.168.1.1');
        $this->assertFalse($private->isLoopback());
    }

    public function testIsLinklocal()
    {
        $linklocal = new IPv4Address('169.254.0.1');
        $this->assertTrue($linklocal->isLinkLocal());
        $this->assertTrue($linklocal->isAPIPA());

        $private = new IPv4Address('192.168.1.1');
        $this->assertFalse($private->isLinkLocal());
        $this->assertFalse($private->isAPIPA());
    }

    public function testIsMulticast()
    {
        $multicast = new IPv4Address('224.0.0.1');
        $this->assertTrue($multicast->isMulticast());

        $private = new IPv4Address('192.168.1.1');
        $this->assertFalse($private->isMulticast());
    }

    /**
     * @dataProvider providerForTestIsPrivate
     */
    public function testIsPrivate($input, $expected_result)
    {
        $ip = new IPv4Address($input);
        $this->assertEquals($expected_result, $ip->isPrivate());
    }

    public function providerForTestIsPrivate()
    {
        return [
            ['0.0.0.0', true],
            ['0.0.0.1', true],
            ['10.0.0.1', true],
            ['127.0.0.1', true],
            ['169.254.0.1', true],
            ['172.16.0.1', true],
            ['192.0.0.1', true],
            ['192.0.0.171', true],
            ['192.0.2.1', true],
            ['192.168.0.1', true],
            ['198.18.0.1', true],
            ['198.51.100.1', true],
            ['203.0.113.1', true],
            ['240.0.0.1', true],
            ['255.255.255.255', true],

            ['8.8.8.8', false],
            ['1.1.1.1', false],
        ];
    }

    /**
     * @dataProvider providerForTestIsPublic
     */
    public function testIsPublic($input, $expected_result)
    {
        $ip = new IPv4Address($input);
        $this->assertEquals($expected_result, $ip->isPublic());
        $this->assertEquals($expected_result, $ip->isGlobal());
    }

    public function providerForTestIsPublic()
    {
        return [
            ['0.0.0.0', false],
            ['0.0.0.1', false],
            ['10.0.0.1', false],
            ['127.0.0.1', false],
            ['169.254.0.1', false],
            ['172.16.0.1', false],
            ['192.0.0.1', false],
            ['192.0.0.171', false],
            ['192.0.2.1', false],
            ['192.168.0.1', false],
            ['198.18.0.1', false],
            ['198.51.100.1', false],
            ['203.0.113.1', false],
            ['240.0.0.1', false],
            ['255.255.255.255', false],

            ['8.8.8.8', true],
            ['1.1.1.1', true],
        ];
    }

    /**
     * @dataProvider providerForTestClass
     */
    public function testClass($input, $expected_result)
    {
        $ip = new IPv4Address($input);
        $this->assertEquals($expected_result, $ip->class());
    }

    public function providerForTestClass()
    {
        return [
            ['10.0.0.1', 'A'],
            ['172.16.0.1', 'B'],
            ['192.168.1.1', 'C'],
            ['224.0.0.1', 'D'],
            ['240.0.0.1', 'E'],
        ];
    }
}