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
}