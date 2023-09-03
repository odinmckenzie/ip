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

    /**
     * @dataProvider providerForTestHostMask
     */
    public function testHostMask($prefix, $expected)
    {
        $mask = new IPv4Mask($prefix);
        $this->assertEquals($expected, $mask->hostMask());
    }

    public function providerForTestHostMask()
    {
        return [
            [0, '255.255.255.255'],
            [1, '127.255.255.255'],
            [2, '63.255.255.255'],
            [3, '31.255.255.255'],
            [4, '15.255.255.255'],
            [5, '7.255.255.255'],
            [6, '3.255.255.255'],
            [7, '1.255.255.255'],
            [8, '0.255.255.255'],
            [9, '0.127.255.255'],
            [10, '0.63.255.255'],
            [11, '0.31.255.255'],
            [12, '0.15.255.255'],
            [13, '0.7.255.255'],
            [14, '0.3.255.255'],
            [15, '0.1.255.255'],
            [16, '0.0.255.255'],
            [17, '0.0.127.255'],
            [18, '0.0.63.255'],
            [19, '0.0.31.255'],
            [20, '0.0.15.255'],
            [21, '0.0.7.255'],
            [22, '0.0.3.255'],
            [23, '0.0.1.255'],
            [24, '0.0.0.255'],
            [25, '0.0.0.127'],
            [26, '0.0.0.63'],
            [27, '0.0.0.31'],
            [28, '0.0.0.15'],
            [29, '0.0.0.7'],
            [30, '0.0.0.3'],
            [31, '0.0.0.1'],
            [32, '0.0.0.0']
        ];
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