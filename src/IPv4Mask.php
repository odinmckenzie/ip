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
            } elseif ($binary_str == '00000000000000000000000000000000') {
                $this->prefix = 0;
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

    public function __toString(): string
    {
        return (string) $this->prefix;
    }

    public function subnetMask(): string
    {
        if ($this->prefix == 0) {
            return '0.0.0.0';
        } else {
            $subnet_mask_long = -1 << (32 - $this->prefix);

            return long2ip($subnet_mask_long);
        }
    }

    public function hostMask(): string
    {
        $host_mask_long = ~(-1 << (32 - $this->prefix));

        return long2ip($host_mask_long);
    }

    public function networkSize(): int
    {
        $num_ips = pow(2, (32 - $this->prefix())) - 2;
        $num_ips = abs($num_ips);

        return $num_ips;
    }

    public static function fromNetworkSize(int $size): self
    {
        if ($size > 4294967294 || $size < 0) {
            throw new \InvalidArgumentException("Size value of '$size' must be from 0 to 4294967294, inclusive.");
        }

        // floating point rounding error occurs for 2147483646
        if ($size == 2147483646) {
            return new self(1);
        }

        $prefix = 32 - (int) ceil(log($size + 2, 2));

        return new self($prefix);
    }

    public static function fromClassDefault(string $class): IPv4Mask
    {
        $accepted_classes = ['A', 'a', 'B', 'C', 'c'];
        $class = strtoupper($class);

        if (in_array($class, $accepted_classes) === false) {
            throw new \InvalidArgumentException("'$class' must be either 'A', 'B', or 'C'. 
                The other classes 'D' and 'E' do not have default netmasks.");
        }

        switch ($class) {
            case 'A':
                return new IPv4Mask(8);
            case 'B':
                return new IPv4Mask(16);
            case 'C':
                return new IPv4Mask(24);
        }
    }
}