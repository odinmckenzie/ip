<?php

namespace Odin\IP;

class Address
{
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

    public static function toFormattedBinary($ip, $netmask, string $gap = null): string
    {
        if (!isset($gap)) {
            $gap = '';
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

            $result .= $binary_with_gap[$i];

            if ($binary_with_gap[$i] == ' ') {
                continue;
            } else {
                $count++;
            }
        }

        return $result;
    }
}