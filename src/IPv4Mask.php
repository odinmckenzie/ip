<?php

namespace Odin\IP;

class InvalidNetmaskException extends \Exception
{
}

class IPv4Mask
{
    private $prefix;

    public function __construct($mask)
    {
        $mask_val = $mask;

        if (is_string($mask_val) && substr_count($mask_val, '/') > 0) 
            $mask_val = str_replace('/', ' ', $mask_val);

        if (is_string($mask_val))
            $mask_val = trim($mask_val);

        if (is_string($mask_val) && ctype_digit($mask_val))
            $mask_val = (int) $mask_val;

        if (is_int($mask_val)) {
            if ($mask_val >= 0 && $mask_val <= 32) {
                $this->prefix = $mask_val;
            } else {
                throw new InvalidNetmaskException("'$mask' must be an integer between 0 and 32, inclusive.");
            }
        }

        if (is_string($mask_val) && filter_var($mask_val, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $binary_str = decbin(ip2long($mask_val));
            $binary_str = str_pad($binary_str, 32, "0", STR_PAD_LEFT);

            if (preg_match('/^1+0*$/', $binary_str)) {
                $this->prefix = substr_count($binary_str, '1');
            } elseif (preg_match('/^0+1*$/', $binary_str)) {
                $this->prefix = substr_count($binary_str, '0');
            } else {
                throw new InvalidNetmaskException("'$mask' must be either a valid subnet mask or a valid host mask.");
            }
        }

        if (!isset($this->prefix)) {
            if (is_float($mask) && floor($mask) === $mask)
                $mask = sprintf("%.1f", $mask);

            throw new InvalidNetmaskException("'$mask' must be either a prefix length from 0 to 32 or a valid subnet mask or a valid host mask.");
        }
    }

    public function prefix(): int
    {
        return $this->prefix;
    }

    public function subnetMask(): string
    {
        return long2ip(-1 << (32 - $this->prefix));
    }

    public function hostMask(): string
    {
        return long2ip(~(-1 << (32 - $this->prefix)));
    }
}