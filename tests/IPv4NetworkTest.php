<?php

use PHPUnit\Framework\TestCase;
use Odin\IP\IPv4Address;
use Odin\IP\IPv4Network;
use Odin\IP\IPv4Constants;
use Odin\IP\IllegalOperationException;

class IPv4NetworkTest extends TestCase
{
    public function testSimpleObjCreate()
    {
        $net = new IPv4Network('192.168.1.1', 24);
        $this->assertEquals('192.168.1.0', $net->address());

        $net = new IPv4Network('192.168.1.1');
        $this->assertEquals('192.168.1.0', $net->address());
        $this->assertEquals(24, $net->mask()->prefix());
    }

    public function testFrom()
    {
        $net = IPv4Network::from('192.168.1.1/24');
        $this->assertEquals('192.168.1.0', $net->address());
        $this->assertEquals(24, $net->mask()->prefix());
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

        $expected = [
            IPv4Address::from('192.168.1.1/30'), 
            IPv4Address::from('192.168.1.2/30')
        ];
        
        $this->assertEquals($expected, $net->hosts());
    }

    public function testBroadcast()
    {
        $net = new IPv4Network('192.168.1.0', 24);
        
        $this->assertEquals(new IPv4Address('192.168.1.255', 24), $net->broadcast());
    }

    public function testFirstIP()
    {
        $net = new IPv4Network('192.168.1.0', 24);
        
        $this->assertEquals(new IPv4Address('192.168.1.1', 24), $net->firstIP());
    }

    public function testLastIP()
    {
        $net = new IPv4Network('192.168.1.0', 24);
        
        $this->assertEquals(new IPv4Address('192.168.1.254', 24), $net->lastIP());
    }

    public function testContains()
    {
        $net = new IPv4Network('192.168.1.0', 30);

        $this->assertTrue($net->contains('192.168.1.1'));
    }

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

    public function testIsLoopback()
    {
        $loopback = new IPv4Network('127.0.0.1', 32);
        $this->assertTrue($loopback->isLoopback());

        $private = new IPv4Network('192.168.1.1', 24);
        $this->assertFalse($private->isLoopback());
    }

    public function testIsLinklocal()
    {
        $linklocal = new IPv4Network('169.254.0.1', 24);
        $this->assertTrue($linklocal->isLinkLocal());
        $this->assertTrue($linklocal->isAPIPA());

        $private = new IPv4Network('192.168.1.1', 24);
        $this->assertFalse($private->isLinkLocal());
        $this->assertFalse($private->isAPIPA());
    }

    public function testIsMulticast()
    {
        $multicast = new IPv4Network('224.0.0.1', 32);
        $this->assertTrue($multicast->isMulticast());

        $private = new IPv4Network('192.168.1.1', 24);
        $this->assertFalse($private->isMulticast());
    }

    /**
     * @dataProvider providerForTestIsPrivate
     */
    public function testIsPrivate($input, $expected_result)
    {
        $ip = new IPv4Network($input, 32);
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
        $ip = new IPv4Network($input, 32);
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

    public function testToString()
    {
        $net = IPv4Network::from('192.168.1.0/24');

        $this->assertEquals('192.168.1.0/24', (string) $net);
    }

    public function testSubnetsCount()
    {
        $net = IPv4Network::from('192.168.1.0/24');
        
        $this->assertEquals(4, $net->subnetsCount('/26'));
    }

    public function testSubnetsCountInvalidInput()
    {
        $this->expectException(IllegalOperationException::class);
        $this->expectExceptionMessage("The new mask '/24' cannot be less than the current mask of '/26' for this operation.");

        $net = IPv4Network::from('192.168.1.0/26');
        $net->subnetsCount('/24');
    }

    public function testSubnets()
    {
        $net = IPv4Network::from('192.168.1.0/24');
        
        $expected_result = [
            IPv4Network::from('192.168.1.0/26'),
            IPv4Network::from('192.168.1.64/26'),
            IPv4Network::from('192.168.1.128/26'),
            IPv4Network::from('192.168.1.192/26'),
        ];
        
        $this->assertEquals($expected_result, $net->subnets('/26'));
    }

    public function testSubnetsInvalidInput()
    {
        $this->expectException(IllegalOperationException::class);
        $this->expectExceptionMessage("The new mask '/24' cannot be less than the current mask of '/26' for this operation.");

        $net = IPv4Network::from('192.168.1.0/26');
        $net->subnets('/24');
    }

    public function testClassfulSubnetsCount()
    {
        $net = IPv4Network::from('192.168.1.0/26');
        $this->assertEquals(4, $net->classfulSubnetsCount());
    }

    public function testClassfulSubnetsCountInvalidInput()
    {
        $this->expectException(IllegalOperationException::class);
        $this->expectExceptionMessage("The default mask of '/24' cannot be greater than the current mask of '/22' for this operation.");

        $net = IPv4Network::from('192.168.1.0/22');
        $net->classfulSubnetsCount();
    }

    public function testClassfulSubnets()
    {
        $net = IPv4Network::from('192.168.1.64/26');
        
        $expected_result = [
            IPv4Network::from('192.168.1.0/26'),
            IPv4Network::from('192.168.1.64/26'),
            IPv4Network::from('192.168.1.128/26'),
            IPv4Network::from('192.168.1.192/26'),
        ];
        
        $this->assertEquals($expected_result, $net->classfulSubnets());
    }

    public function testClassfulSubnetsInvalidInput()
    {
        $this->expectException(IllegalOperationException::class);
        $this->expectExceptionMessage("The default mask of '/24' cannot be greater than the current mask of '/22' for this operation.");

        $net = IPv4Network::from('192.168.1.0/22');
        $net->classfulSubnetsCount();
    }
}