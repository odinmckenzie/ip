<?php

use PHPUnit\Framework\TestCase;
use Odin\IP\IPv4Address;
use Odin\IP\IPv4Network;
use Odin\IP\IPv4Mask;
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

    public function testToString()
    {
        $ip = new IPv4Address('192.168.1.2 ');
        $ip_str = (string) $ip;

        $this->assertEquals('192.168.1.2', $ip_str);
    }

    public function testNetworkId()
    {
        $ip = new IPv4Address('192.168.1.2');
        $net = $ip->network(24);

        $this->assertEquals('192.168.1.0', $net->address());
    }

    public function testHostId()
    {
        $ip = new IPv4Address('192.168.1.2');
        $host_id = $ip->hostId(24);

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
        $ip = new IPv4Address($address);
        $this->assertEquals($expected_binary, $ip->toFormattedBinary($netmask, $gap));
    }

    public function toFormattedBinaryProvider()
    {
        return [
            ['10.1.1.1', '/8', ' ', '00001010. 00000001.00000001.00000001'],
            ['192.168.0.1', 24, '', '11000000.10101000.00000000.00000001'],
            ['255.255.255.0', new IPv4Mask(24), ' ', '11111111.11111111.11111111. 00000000'],
        ];
    }

    public function testLinkLocalNetwork()
    {
        $expected_network = IPv4Network::from('169.254.0.0/16');

        $linkLocalNetwork = IPv4Network::linkLocalNetwork();
        $this->assertEquals($expected_network, $linkLocalNetwork);

        $linkLocalNetwork = IPv4Network::apipaNetwork();
        $this->assertEquals($expected_network, $linkLocalNetwork);
    }

    public function testLoopbackNetwork()
    {
        $expected_network = IPv4Network::from('127.0.0.0/8');

        $loopbackNetwork = IPv4Network::loopbackNetwork();
        $this->assertEquals($expected_network, $loopbackNetwork);
    }

    /**
     * @dataProvider classNetworkProvider
     */
    public function testClassNetwork(string $class, string $expected)
    {
        $network = IPv4Network::classNetwork($class);
        $expected_network = IPv4Network::from($expected);

        $this->assertEquals($expected_network, $network);
    }

    public function classNetworkProvider(): array
    {
        return [
            ['A', '0.0.0.0/1'],
            ['a', '0.0.0.0/1'],

            ['B', '128.0.0.0/2'],
            ['b', '128.0.0.0/2'],

            ['C', '192.0.0.0/3'],
            ['c', '192.0.0.0/3'],

            ['D', '224.0.0.0/4'],
            ['d', '224.0.0.0/4'],

            ['E', '240.0.0.0/4'],
            ['e', '240.0.0.0/4'],
        ];
    }

    public function testInvalidClassNetworkInput()
    {
        $this->expectException(\InvalidArgumentException::class);
        IPv4Network::classNetwork('F');
    }

    public function testMulticastNetwork()
    {
        $expected_network = IPv4Network::from('224.0.0.0/4');

        $multicastNetwork = IPv4Network::multicastNetwork();
        $this->assertEquals($expected_network, $multicastNetwork);
    }

    public function testReservedNetwork()
    {
        $expected_network = IPv4Network::from('240.0.0.0/4');

        $reservedNetwork = IPv4Network::reservedNetwork();
        $this->assertEquals($expected_network, $reservedNetwork);
    }

    public function testPrivateNetwork()
    {
        $expected_network = [
            IPv4Network::from('0.0.0.0/8'),
            IPv4Network::from('10.0.0.0/8'),
            IPv4Network::from('127.0.0.0/8'),
            IPv4Network::from('169.254.0.0/16'),
            IPv4Network::from('172.16.0.0/12'),
            IPv4Network::from('192.0.0.0/29'),
            IPv4Network::from('192.0.0.170/31'),
            IPv4Network::from('192.0.2.0/24'),
            IPv4Network::from('192.168.0.0/16'),
            IPv4Network::from('198.18.0.0/15'),
            IPv4Network::from('198.51.100.0/24'),
            IPv4Network::from('203.0.113.0/24'),
            IPv4Network::from('240.0.0.0/4'),
            IPv4Network::from('255.255.255.255/32'),
        ];

        $privateNetworks = IPv4Network::privateNetworks();
        $this->assertEquals($expected_network, $privateNetworks);
    }
}