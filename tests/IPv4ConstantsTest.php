<?php

use PHPUnit\Framework\TestCase;
use Odin\IP\IPv4Network;
use Odin\IP\IPv4Constants;

class IPv4ConstantsTest extends TestCase
{
    public function testLinkLocalNetwork()
    {
        $expected_network = IPv4Network::from('169.254.0.0/16');

        $linkLocalNetwork = IPv4Constants::linkLocalNetwork();
        $this->assertEquals($expected_network, $linkLocalNetwork);

        $linkLocalNetwork = IPv4Constants::apipaNetwork();
        $this->assertEquals($expected_network, $linkLocalNetwork);
    }

    public function testLoopbackNetwork()
    {
        $expected_network = IPv4Network::from('127.0.0.0/8');

        $loopbackNetwork = IPv4Constants::loopbackNetwork();
        $this->assertEquals($expected_network, $loopbackNetwork);
    }

    /**
     * @dataProvider validClassBitsProvider
     */
    public function testClassBits($class, $expected)
    {
        $this->assertEquals($expected, IPv4Constants::classBits($class));
    }

    /**
     * Data provider for testClassBits.
     */
    public function validClassBitsProvider()
    {
        return [
            ['A', '0000'],
            ['a', '0000'],

            ['B', '1000'],
            ['b', '1000'],

            ['C', '1100'],
            ['c', '1100'],

            ['D', '1110'],
            ['d', '1110'],
            
            ['E', '1111'],
            ['e', '1111'],
        ];
    }

    /**
     * Test invalid IP classes input for classBits().
     */
    public function testInvalidClassBitsInput()
    {
        $this->expectException(InvalidArgumentException::class);
        IPv4Constants::classBits('F');
    }

    /**
     * @dataProvider validClassPrefixProvider
     */
    public function testClassPrefix($class, $expected)
    {
        $this->assertEquals($expected, IPv4Constants::classPrefix($class));
    }

    /**
     * Data provider for testClassPrefix.
     */
    public function validClassPrefixProvider()
    {
        return [
            ['A', '0000'],
            ['a', '0000'],

            ['B', '1000'],
            ['b', '1000'],

            ['C', '1100'],
            ['c', '1100'],
        ];
    }

    /**
     * Test invalid IP classes input for classPrefix().
     */
    public function testInvalidClassPrefixInput()
    {
        $this->expectException(InvalidArgumentException::class);
        IPv4Constants::classPrefix('D');
    }

    /**
     * @dataProvider classNetworkProvider
     */
    public function testClassNetwork(string $class, string $expected)
    {
        $network = IPv4Constants::classNetwork($class);
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
        IPv4Constants::classNetwork('F');
    }

    public function testMulticastNetwork()
    {
        $expected_network = IPv4Network::from('224.0.0.0/4');

        $multicastNetwork = IPv4Constants::multicastNetwork();
        $this->assertEquals($expected_network, $multicastNetwork);
    }

    public function testReservedNetwork()
    {
        $expected_network = IPv4Network::from('240.0.0.0/4');

        $reservedNetwork = IPv4Constants::reservedNetwork();
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
        ];

        $privateNetworks = IPv4Constants::privateNetworks();
        $this->assertEquals($expected_network, $privateNetworks);
    }
}