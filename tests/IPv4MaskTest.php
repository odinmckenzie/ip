<?php

use PHPUnit\Framework\TestCase;
use Odin\IP\IPv4Mask;
use Odin\IP\InvalidNetmaskException;

class IPv4MaskTest extends TestCase
{
    /**
     * @dataProvider maskProvider
     */
    public function testSimpleMaskCreate($input, $expected)
    {
        $mask = new IPv4Mask($input);
        $this->assertEquals($expected, $mask->prefix());
    }

    public function maskProvider()
    {
        return [
            [24, 24],
            [0, 0],
            [32, 32],
            ['24', 24],
            ['/24', 24],

            // Test with string containing spaces and a slash
            ['24 ', 24],
            [' / 24 ', 24],

            // Test with subnet masks
            ['255.255.255.0', 24],
            ['255.255.255.192', 26],
            ['255.255.255.255', 32],
            ['0.0.0.0', 0],

            // Test with host masks
            ['0.0.0.255', 24],
            ['0.0.0.63', 26]
        ];
    }

    /**
     * @dataProvider invalidMaskProvider
     */
    public function testInvalidIPv4MaskInput($input, $expected_message)
    {
        $this->expectException(InvalidNetmaskException::class);
        $this->expectExceptionMessage($expected_message);
        new IPv4Mask($input);
    }

    public function invalidMaskProvider()
    {
        return [
            [-1, "'-1' must be an integer between 0 and 32, inclusive."],
            [33, "'33' must be an integer between 0 and 32, inclusive."],
            [24.0, "'24.0' must be either a prefix length from 0 to 32 or a valid subnet mask or a valid host mask."],
            ['0.255.0.255', "'0.255.0.255' must be either a valid subnet mask or a valid host mask."],
            ['/35', "'/35' must be an integer between 0 and 32, inclusive."],

            ['192.168.1.1/24', "'192.168.1.1/24' must be either a prefix length from 0 to 32 or a valid subnet mask or a valid host mask."],
            ['192.168.1.1/255.255.255.0', "'192.168.1.1/255.255.255.0' must be either a prefix length from 0 to 32 or a valid subnet mask or a valid host mask."],
        ];
    }

    public function testSubnetMask()
    {
        $mask = new IPv4Mask(24);
        $this->assertEquals('255.255.255.0', $mask->subnetMask());

        $mask = new IPv4Mask(0);
        $this->assertEquals('0.0.0.0', $mask->subnetMask());

        $mask = new IPv4Mask(32);
        $this->assertEquals('255.255.255.255', $mask->subnetMask());
    }

    public function testHostMask()
    {
        $mask = new IPv4Mask(24);
        $this->assertEquals('0.0.0.255', $mask->hostMask());
    }

    public function testNetworkSize()
    {
        $mask = new IPv4Mask('/8');
        $this->assertEquals(16777214, $mask->networkSize());

        $mask = new IPv4Mask('/16');
        $this->assertEquals(65534, $mask->networkSize());

        $mask = new IPv4Mask('/24');
        $this->assertEquals(254, $mask->networkSize());
    }

    public function testFromNetworkSize()
    {
        $mask = IPv4Mask::fromNetworkSize(50);
        $this->assertEquals(26, $mask->prefix());

        $mask = IPv4Mask::fromNetworkSize(400);
        $this->assertEquals(23, $mask->prefix());
    }
}