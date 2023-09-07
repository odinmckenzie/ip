<?php

namespace Odin\IP;

/**
 * Exception class for invalid netmask values.
 */
class InvalidNetmaskException extends \Exception
{
}

/**
 * Represents an IPv4 subnet mask and provides various operations related to it.
 */
class IPv4Mask
{
    /**
     * @var int The prefix length of the subnet mask.
     */
    private $prefix;

    /**
     * Constructs an IPv4Mask object based on the provided subnet mask or prefix length.
     *
     * @param mixed $mask A subnet mask (string or integer) or a prefix length (integer).
     *
     * @throws InvalidNetmaskException If the provided mask is invalid.
     */
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

    /**
     * Get the prefix length of the subnet mask.
     *
     * @return int The prefix length.
     */
    public function prefix(): int
    {
        return $this->prefix;
    }

    /**
     * Convert the IPv4Mask object to its string representation.
     *
     * @return string The string representation of the prefix length.
     */
    public function __toString(): string
    {
        return (string) $this->prefix;
    }

    /**
     * Get the subnet mask as an IPv4 address.
     *
     * @return string The subnet mask as an IPv4 address.
     */
    public function subnetMask(): string
    {
        if ($this->prefix == 0) {
            return '0.0.0.0';
        } else {
            $subnet_mask_long = -1 << (32 - $this->prefix);

            return long2ip($subnet_mask_long);
        }
    }

    /**
     * Get the host mask as an IPv4 address.
     *
     * @return string The host mask as an IPv4 address.
     */
    public function hostMask(): string
    {
        $host_mask_long = ~(-1 << (32 - $this->prefix));

        return long2ip($host_mask_long);
    }

    /**
     * Get the size of the network represented by the subnet mask.
     *
     * @return int The size of the network.
     */
    public function networkSize(): int
    {
        $num_ips = pow(2, (32 - $this->prefix())) - 2;
        $num_ips = abs($num_ips);

        return $num_ips;
    }

    /**
     * Convert the IPv4 subnet mask to its binary representation.
     *
     * @return string The binary representation of the subnet mask.
     */
    public function toBinary(): string
    {
        $binary = Address::toBinary($this);

        return $binary;
    }

    /**
     * Convert the IPv4 subnet mask to its formatted binary representation.
     *
     * @param string|null $gap Optional gap separator between octets.
     *
     * @return string The formatted binary representation of the subnet mask.
     */
    public function toFormattedBinary(string $gap = null): string
    {
        $fbinary = Address::toFormattedBinary($this, $this, $gap);

        return $fbinary;
    }

    /**
     * Create an IPv4Mask object based on a desired network size.
     *
     * @param int $size The desired network size.
     *
     * @return IPv4Mask An IPv4Mask object with the closest matching prefix length.
     *
     * @throws InvalidArgumentException If the size is out of range.
     */
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

    /**
     * Create an IPv4Mask object based on the classful default subnet mask for a given IP class.
     *
     * @param string $class The IP class ('A', 'B', or 'C').
     *
     * @return IPv4Mask An IPv4Mask object with the default prefix length for the specified class.
     *
     * @throws InvalidArgumentException If an invalid IP class is provided.
     */
    public static function fromClassDefault(string $class): IPv4Mask
    {
        $accepted_classes = ['A', 'B', 'C'];
        $class = strtoupper($class);

        if (!in_array($class, $accepted_classes)) {
            throw new \InvalidArgumentException("'$class' must be either 'A', 'B', or 'C'. "
                . "The other classes 'D' and 'E' do not have default netmasks.");
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