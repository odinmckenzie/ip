<?php

namespace Odin\IP;

/**
 * Custom exception class for handling invalid IP addresses.
 */
class InvalidAddressException extends \Exception
{
}

/**
 * Represents an IPv4 address.
 */
class IPv4Address
{
    /**
     * @var string The IPv4 address in dotted decimal format.
     */
    protected $ip_str;

    /**
     * @var int The IPv4 address in long format.
     */
    protected $ip_long;

    /**
     * @var IPv4Mask The subnet mask associated with the address.
     */
    protected $netmask;

    /**
     * Initializes a new IPv4Address instance.
     *
     * @param string $ip      The IPv4 address as a string.
     * @param mixed  $netmask The subnet mask (default is '32' for single address).
     *
     * @throws InvalidAddressException If the provided IP address is invalid.
     */
    public function __construct(string $ip, $netmask = null)
    {
        $ip = trim($ip);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->ip_str = $ip;
            $this->ip_long = ip2long($ip);
        } elseif (strpos($ip, '/') !== false) {
            throw new InvalidAddressException("Unexpected '/' found in '$ip'. Use the from() static method instead.");
        } else {
            throw new InvalidAddressException("'$ip' is in an unexpected format.");
        }

        if ($netmask instanceof IPv4Mask) {
            $this->netmask = $netmask;
        } elseif (isset($netmask)) {
            $this->netmask = new IPv4Mask($netmask);
        } else {
            try {
                $class = $this->class();
                $this->netmask = IPv4Mask::fromClass($class);
            } catch (\InvalidArgumentException $e) {
                $this->netmask = new IPv4Mask(32);
            }
        }
    }

    /**
     * Creates an IPv4Address instance from a string.
     *
     * @param string $ip The IPv4 address as a string.
     *
     * @return IPv4Address The IPv4Address instance.
     */
    public static function from(string $ip)
    {
        if (strpos($ip, '/') === false) {
            return new self($ip);
        } else {
            list($ip_str, $netmask) = explode('/', $ip);

            return new self($ip_str, $netmask);
        }
    }

    /**
     * Gets the IPv4 address as a string.
     *
     * @return string The IPv4 address.
     */
    public function address(): string
    {
        return $this->ip_str;
    }

    /**
     * Gets the subnet mask associated with the address.
     *
     * @return IPv4Mask The subnet mask.
     */
    public function mask(): IPv4Mask
    {
        return $this->netmask;
    }

    /**
     * Gets the IPv4 network containing this address.
     *
     * @return IPv4Network The IPv4 network.
     */
    public function network(): IPv4Network
    {
        return new IPv4Network($this->address(), $this->mask());
    }

    /**
     * Converts the IPv4 address to a string.
     *
     * @return string The IPv4 address in "IP/prefix" format.
     */
    public function __toString(): string
    {
        $net = $this->address();
        $prefix = $this->mask()->prefix();

        return "$net/$prefix";
    }

    /**
     * Converts the IPv4 address to an integer.
     *
     * @return int The IPv4 address as an integer.
     */
    public function toInt(): int
    {
        return $this->ip_long;
    }

    /**
     * Gets the IP version (IPv4).
     *
     * @return int The IP version.
     */
    public function version(): int
    {
        return 4;
    }

    /**
     * Gets the host ID part of the IPv4 address.
     *
     * @return string The host ID.
     */
    public function hostId(): string
    {
        $host_mask_long = ip2long($this->mask()->hostMask());
        $host_id_long = $this->ip_long& $host_mask_long;

        return long2ip($host_id_long);
    }

    /**
     * Adds an integer to the IPv4 address and returns a new IPv4Address instance.
     *
     * @param int $increment The integer to add.
     *
     * @return IPv4Address The new IPv4Address instance.
     *
     * @throws InvalidAddressException If the result is out of range.
     */
    public function add(int $increment): IPv4Address
    {
        $next_ip_long = $this->ip_long + $increment;

        $ip = $this->address();

        if ($next_ip_long > 4294967295) {
            throw new InvalidAddressException("'$ip' + $increment is greater than '255.255.255.255'");
        } elseif ($next_ip_long < 0) {
            throw new InvalidAddressException("'$ip' + $increment is less than '0.0.0.0'");
        }

        $next_ip = long2ip($next_ip_long);

        return new IPv4Address($next_ip, $this->mask());
    }

    /**
     * Subtracts an integer from the IPv4 address and returns a new IPv4Address instance.
     *
     * @param int $increment The integer to subtract.
     *
     * @return IPv4Address The new IPv4Address instance.
     *
     * @throws InvalidAddressException If the result is out of range.
     */
    public function subtract(int $increment): IPv4Address
    {
        $next_ip_long = $this->ip_long - $increment;

        $ip = $this->address();

        if ($next_ip_long < 0) {
            throw new InvalidAddressException("'$ip' - $increment is less than '0.0.0.0'");
        } elseif ($next_ip_long > 4294967295) {
            throw new InvalidAddressException("'$ip' - $increment is greater than '255.255.255.255'");
        }

        $next_ip = long2ip($next_ip_long);

        return new IPv4Address($next_ip, $this->mask());
    }

    /**
     * Checks if the IPv4 address is an unspecified address (all zeros).
     *
     * @return bool True if it's an unspecified address, false otherwise.
     */
    public function isUnspecified(): bool
    {
        return $this->ip_long == 0;
    }

    /**
     * Converts the IPv4 address to binary representation.
     *
     * @return string The binary representation of the IPv4 address.
     */
    public function toBinary(): string
    {
        $binary = Address::toBinary($this);

        return $binary;
    }

    /**
     * Converts the IPv4 address to formatted binary representation.
     *
     * @param mixed  $netmask The subnet mask (optional).
     * @param string $gap     The character to separate octets (optional).
     *
     * @return string The formatted binary representation.
     */
    public function toFormattedBinary(string $gap = null): string
    {
        $netmask = $this->mask();

        return Address::toFormattedBinary($this, $netmask, $gap);
    }

    /**
     * Gets the class of the IPv4 address (A, B, C, D, E).
     *
     * @return string The class of the IPv4 address.
     */
    public function class(): string
    {
        $classes = ['A', 'B', 'C', 'D', 'E'];

        $ip = $this->address();
        $first_octet = intval(explode('.', $ip)[0]);
        $first_octet_bin = decbin($first_octet);
        $first_octet_bin = str_pad($first_octet_bin, 8, "0", STR_PAD_LEFT);

        foreach ($classes as $class) {
            $class_bits = IPv4Constants::classBits($class);
            $ip_class_bits = substr($first_octet_bin, 0, strlen($class_bits));

            if($class_bits === $ip_class_bits) {
                return $class;
            }
        }
    }

    /**
     * Checks if the IPv4 address is a loopback address.
     *
     * @return bool True if it's a loopback address, false otherwise.
     */
    public function isLoopback(): bool
    {
        return IPv4Constants::loopbackNetwork()->contains($this->address());
    }

    /**
     * Checks if the IPv4 address is a link-local address.
     *
     * @return bool True if it's a link-local address, false otherwise.
     */
    public function isLinkLocal(): bool
    {
        return IPv4Constants::linkLocalNetwork()->contains($this->address());
    }

    /**
     * Checks if the IPv4 address is an APIPA (Automatic Private IP Addressing) address.
     *
     * @return bool True if it's an APIPA address, false otherwise.
     */
    public function isAPIPA(): bool
    {
        return $this->isLinkLocal();
    }

    /**
     * Checks if the IPv4 address is a multicast address.
     *
     * @return bool True if it's a multicast address, false otherwise.
     */
    public function isMulticast(): bool
    {
        return IPv4Constants::multicastNetwork()->contains($this->address());
    }

    /**
     * Checks if the IPv4 address is a private address.
     *
     * @return bool True if it's a private address, false otherwise.
     */
    public function isPrivate(): bool
    {
        foreach (IPv4Constants::privateNetworks() as $private_net) {
            if ($private_net->contains($this->address())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the IPv4 address is a public address.
     *
     * @return bool True if it's a public address, false otherwise.
     */
    public function isPublic(): bool
    {
        return !$this->isPrivate();
    }

    /**
     * Checks if the IPv4 address is a global address (same as public).
     *
     * @return bool True if it's a global address, false otherwise.
     */
    public function isGlobal(): bool
    {
        return $this->isPublic();
    }
}