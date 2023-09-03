<?php

namespace Odin\IP;

use Odin\IP\IPv4Mask;

class Address
{
    public static function toBinary($input): string
    {
        $result = '';

        switch (true) {
            case ($input instanceof IPv4Address) || ($input instanceof IPv4Network):
                $mask_long = ip2long($input->address());
                $binary = decbin($mask_long);
                $binary = str_pad($binary, 32, "0", STR_PAD_LEFT);
                // make sure the IP binary is 32 bits long by adding leading zeroes
                $result = str_pad($binary, 32, "0", STR_PAD_LEFT);
                break;
            case ($input instanceof IPv4Mask):
                $mask_long = ip2long($input->subnetMask());
                $binary = decbin($mask_long);
                $binary = str_pad($binary, 32, "0", STR_PAD_LEFT);
                // make sure the netmask binary is 32 bits long by adding leading zeroes
                $result = str_pad($binary, 32, "0", STR_PAD_LEFT);
                break;
        }

        return $result;
    }

    public static function toFormattedBinary($ip, $netmask, string $gap = ' '): string
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