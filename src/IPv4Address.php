<?php

namespace Odin\IP;

class InvalidAddressException extends \Exception
{
}

class IPv4Address
{
    protected $ip;

    public function __construct($ip)
    {
        $ip = trim($ip);

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (strpos($ip, '/') !== false) {
                throw new InvalidAddressException("Unexpected '/' found in '$ip'");
            } else {
                throw new InvalidAddressException("'$ip' is in an unexpected format");
            }
        }

        $this->ip = ip2long($ip);
    }

    public function address(): string
    {
        return long2ip($this->ip);
    }

    public function __toString(): string
    {
        return $this->address();
    }

    public function version(): int
    {
        return 4;
    }

    public function hostId($mask): string
    {
        if ($mask instanceof IPv4Mask) {
            $netmask = $mask;
        } else {
            $netmask = new IPv4Mask($mask);
        }

        $host_mask_long = ip2long($netmask->hostMask());
        $host_id_long = $this->ip & $host_mask_long;

        $host_id = long2ip($host_id_long);

        return $host_id;
    }

    public function add(int $increment): IPv4Address
    {
        $next_ip_long = $this->ip + $increment;

        $ip = $this->address();

        if ($next_ip_long > 4294967295) {
            throw new InvalidAddressException("'$ip' + $increment is greater than '255.255.255.255'");
        } elseif ($next_ip_long < 0) {
            throw new InvalidAddressException("'$ip' + $increment is less than '0.0.0.0'");
        }

        $next_ip = long2ip($next_ip_long);

        return new IPv4Address($next_ip);
    }

    public function subtract(int $increment): IPv4Address
    {
        $next_ip_long = $this->ip - $increment;

        $ip = $this->address();

        if ($next_ip_long < 0) {
            throw new InvalidAddressException("'$ip' - $increment is less than '0.0.0.0'");
        } elseif ($next_ip_long > 4294967295) {
            throw new InvalidAddressException("'$ip' - $increment is greater than '255.255.255.255'");
        }

        $next_ip = long2ip($next_ip_long);

        return new IPv4Address($next_ip);
    }

    public function isUnspecified(): bool
    {
        return $this->ip == 0;
    }
}