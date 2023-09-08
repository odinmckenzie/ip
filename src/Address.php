<?php

namespace Odin\IP;

class Address
{

    /**
     * Creates an IPv4Address object using either the constructor or from() method of IPv4Address.
     *
     * @param string $ip The IPv4 address as a string.
     * @param IPv4Mask|string  $netmask The subnet mask (default is '32' for single address).
     *
     * @return IPv4Address The IPv4Address instance.
     */
    public static function from(string $ip, $netmask = null)
    {
        if ($netmask === null) {
            return IPv4Address::from($ip);
        } else {
            return new IPv4Address($ip, $netmask);
        }
    }

    /**
     * Converts an IPv4 address or IPv4 netmask to its binary representation.
     *
     * This method accepts an IPv4Address, IPv4Network, or IPv4Mask object and
     * returns its binary representation as a string.
     *
     * @param IPv4Address|IPv4Network|IPv4Mask $input The input object to convert.
     *
     * @return string The binary representation of the input.
     *
     * @throws \InvalidArgumentException If the input is not of type IPv4Address, IPv4Network, or IPv4Mask.
     *
     * @see IPv4Address, IPv4Network, IPv4Mask
     */
    public static function toBinary($input): string
    {
        if ($input instanceof IPv4Address || $input instanceof IPv4Network) {
            $long_value = $input->toInt();
        } elseif ($input instanceof IPv4Mask) {
            $long_value = ip2long($input->subnetMask());
        } else {
            throw new \InvalidArgumentException("The value is not of type IPv4Address or IPv4Network or IPv4Mask.");
        }

        $binary_str = decbin($long_value);
        $binary_str = str_pad($binary_str, 32, '0', STR_PAD_LEFT);

        return $binary_str;
    }

    /**
     * Converts an IPv4 address and netmask to formatted binary representation.
     *
     * This method takes an IPv4 address and a netmask (either as an IPv4Mask
     * object or a string) and returns the binary representation with optional
     * gap characters.
     *
     * @param IPv4Address|IPv4Network|string $ip The IPv4 address to convert.
     * @param IPv4Mask|string $netmask The netmask, either as an IPv4Mask object or a string.
     * @param string|null $gap Optional gap characters to insert between the binary octets.
     *
     * @return string The formatted binary representation of the address and netmask.
     *
     * @see IPv4Address, IPv4Mask
     */
    public static function toFormattedBinary($ip, $netmask, string $gap = null): string
    {
        $gap = $gap ?? '';

        if (is_string($ip)) {
            $ip = new IPv4Address($ip, $netmask);
            $netmask = $ip->mask();
        }

        if ($netmask instanceof IPv4Mask) {
            $mask = $netmask;
        } else {
            $mask = new IPv4Mask($netmask);
        }

        $binary = $ip->toBinary();
        $prefix = $mask->prefix();

        $binary_with_gap = substr($binary, 0, $prefix) . $gap . substr($binary, $prefix);

        // add dots every 8 bits
        $result = '';
        $count = 0;
        for ($i = 0; $i < strlen($binary_with_gap); $i++) {
            if ($count == 8) {
                $result .= '.';
                $count = 0;
            }

            if ($binary_with_gap[$i] !== ' ') {
                $count++;
            }

            $result .= $binary_with_gap[$i];
        }

        return $result;
    }
}