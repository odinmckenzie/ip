<?php

use PHPUnit\Framework\TestCase;
use Odin\IP\IPv4Mask;
use Odin\IP\InvalidNetmaskException;

class IPv4MaskTest extends TestCase
{
    /**
     * @dataProvider maskProvider
     */
    public function testSimpleMaskCreate($mask, $expectedPrefix)
    {
        $mask = new IPv4Mask($mask);
        $this->assertEquals($expectedPrefix, $mask->prefix());
    }

    public function maskProvider()
    {
        return [
            // mask value, expected prefix
            [0, 0],
            [32, 32],
            [24, 24],
            ['24', 24],
            ['/24', 24],

            // Test with string containing spaces and a slash
            ['24 ', 24],
            [' / 24 ', 24],
            ['255.255.255.0 ', 24],
            ['0.0.0.255 ', 24],

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
    public function testInvalidIPv4MaskInput($mask, $expected_message)
    {
        $this->expectException(InvalidNetmaskException::class);
        $this->expectExceptionMessage($expected_message);
        new IPv4Mask($mask);
    }

    public function invalidMaskProvider()
    {
        return [
            // mask value, expected error message
            [-1, "'-1' must be an integer between 0 and 32, inclusive."],
            [33, "'33' must be an integer between 0 and 32, inclusive."],
            [24.0, "'24.0' must be either a prefix length from 0 to 32 or a valid subnet mask or a valid host mask."],
            ['0.255.0.255', "'0.255.0.255' must be either a valid subnet mask or a valid host mask."],
            ['255.255.255 .0', "'255.255.255 .0' must be either a prefix length from 0 to 32 or a valid subnet mask or a valid host mask."],
            ['255.255.256.0', "'255.255.256.0' must be either a prefix length from 0 to 32 or a valid subnet mask or a valid host mask."],
            ['/35', "'/35' must be an integer between 0 and 32, inclusive."],

            ['192.168.1.1/24', "'192.168.1.1/24' must be either a prefix length from 0 to 32 or a valid subnet mask or a valid host mask."],
            ['192.168.1.1/255.255.255.0', "'192.168.1.1/255.255.255.0' must be either a prefix length from 0 to 32 or a valid subnet mask or a valid host mask."],
        ];
    }

    public function testComparisons()
    {
        $this->assertTrue(new IPv4Mask('255.255.255.0') == new IPv4Mask('255.255.255.0'));
        $this->assertTrue(new IPv4Mask('255.255.255.0') != new IPv4Mask('255.255.0.0'));

        $this->assertTrue(new IPv4Mask('255.255.255.0') > new IPv4Mask('255.255.0.0'));
        $this->assertTrue(new IPv4Mask('255.255.0.0') < new IPv4Mask('255.255.255.0'));

        $this->assertEquals(-1, new IPv4Mask('255.255.0.0') <=> new IPv4Mask('255.255.255.0'));
        $this->assertEquals(0, new IPv4Mask('255.255.255.0') <=> new IPv4Mask('255.255.255.0'));
        $this->assertEquals(1, new IPv4Mask('255.255.255.0') <=> new IPv4Mask('255.255.0.0'));
    }

    /**
     * @dataProvider providerForTestSubnetMask
     */
    public function testSubnetMask($prefix, $expectedSubnetMask)
    {
        $mask = new IPv4Mask($prefix);
        $this->assertEquals($expectedSubnetMask, $mask->subnetMask());
    }

    public function providerForTestSubnetMask()
    {
        return [
            // prefix, expected subnet mask
            [0, '0.0.0.0'],
            [1, '128.0.0.0'],
            [2, '192.0.0.0'],
            [3, '224.0.0.0'],
            [4, '240.0.0.0'],
            [5, '248.0.0.0'],
            [6, '252.0.0.0'],
            [7, '254.0.0.0'],
            [8, '255.0.0.0'],
            [9, '255.128.0.0'],
            [10, '255.192.0.0'],
            [11, '255.224.0.0'],
            [12, '255.240.0.0'],
            [13, '255.248.0.0'],
            [14, '255.252.0.0'],
            [15, '255.254.0.0'],
            [16, '255.255.0.0'],
            [17, '255.255.128.0'],
            [18, '255.255.192.0'],
            [19, '255.255.224.0'],
            [20, '255.255.240.0'],
            [21, '255.255.248.0'],
            [22, '255.255.252.0'],
            [23, '255.255.254.0'],
            [24, '255.255.255.0'],
            [25, '255.255.255.128'],
            [26, '255.255.255.192'],
            [27, '255.255.255.224'],
            [28, '255.255.255.240'],
            [29, '255.255.255.248'],
            [30, '255.255.255.252'],
            [31, '255.255.255.254'],
            [32, '255.255.255.255']
        ];
    }

    /**
     * @dataProvider providerForTestHostMask
     */
    public function testHostMask($prefix, $expectedHostMask)
    {
        $mask = new IPv4Mask($prefix);
        $this->assertEquals($expectedHostMask, $mask->hostMask());
    }

    public function providerForTestHostMask()
    {
        return [
            // prefix, expected host mask
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

    /**
     * @dataProvider providerForTestNetworkSize
     */
    public function testNetworkSize($prefix, $expectedSize)
    {
        $mask = new IPv4Mask($prefix);
        $this->assertEquals($expectedSize, $mask->networkSize());
    }

    public function providerForTestNetworkSize()
    {
        return [
            // prefix, expected size
            ['/0', 4294967294],
            ['/1', 2147483646],
            ['/8', 16777214],
            ['/16', 65534],
            ['/24', 254],
            ['/30', 2],
            ['/31', 0],
            ['/32', 1]
        ];
    }

    /**
     * @dataProvider networkSizeProvider
     */
    public function testFromNetworkSize($networkSize, $expectedPrefix)
    {
        $mask = IPv4Mask::fromNetworkSize($networkSize);
        $this->assertEquals($expectedPrefix, $mask->prefix());
    }

    public function networkSizeProvider()
    {
        return [
            // network size, expected prefix
            [0, 31],
            [1, 30],
            [2, 30],
            [50, 26],
            [400, 23],
            [2147483645, 1],
            [2147483646, 1], // Test for rounding error
            [2147483647, 0],
            [4294967293, 0],
            [4294967294, 0],
        ];
    }

    public function testInvalidFromNetworkSizeInput()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Size value of '4294967295' must be from 0 to 4294967294, inclusive.");

        IPv4Mask::fromNetworkSize(4294967295);
    }

    public function testToString()
    {
        $mask = new IPv4Mask(24);
        $mask_str = (string) $mask;

        $this->assertEquals('24', $mask_str);
    }

    public function testFromClassDefault()
    {
        $a = IPv4Mask::fromClassDefault('A');
        $this->assertEquals(new IPv4Mask(8), $a);

        $b = IPv4Mask::fromClassDefault('B');
        $this->assertEquals(new IPv4Mask(16), $b);

        $c = IPv4Mask::fromClassDefault('C');
        $this->assertEquals(new IPv4Mask(24), $c);
    }

    /**
     * @dataProvider invalidClassProvider
     */
    public function testInvalidFromClassDefaultInput($class, $expected_message)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expected_message);
        IPv4Mask::fromClassDefault($class);
    }

    public function invalidClassProvider()
    {
        return [
            // class value, expected error message
            ['D', "'D' must be either 'A', 'B', or 'C'. The other classes 'D' and 'E' do not have default netmasks."],
            ['E', "'E' must be either 'A', 'B', or 'C'. The other classes 'D' and 'E' do not have default netmasks."],
            ['F', "'F' must be either 'A', 'B', or 'C'. The other classes 'D' and 'E' do not have default netmasks."],
        ];
    }

    public function testToBinary()
    {
        $netmask = new IPv4Mask(24);
        $this->assertEquals('11111111111111111111111100000000', $netmask->toBinary());
    }

    public function testToFormattedBinary()
    {
        $netmask = new IPv4Mask(24);
        $this->assertEquals('11111111.11111111.11111111.00000000', $netmask->toFormattedBinary());
        $this->assertEquals('11111111.11111111.11111111. 00000000', $netmask->toFormattedBinary(' '));
    }
}