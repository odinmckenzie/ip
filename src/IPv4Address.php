<?php

namespace Odin\IP;

class InvalidAddressException extends \Exception
{
}

class IPv4Address
{
    protected $ip_long;
    protected $netmask;

    public function __construct(string $ip, $netmask = '32')
    {
        $ip = trim($ip);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->ip_long = ip2long($ip);
        } elseif (strpos($ip, '/') !== false) {
            throw new InvalidAddressException("Unexpected '/' found in '$ip'. Use the from() static method instead.");
        } else {
            throw new InvalidAddressException("'$ip' is in an unexpected format.");
        }

        if ($netmask instanceof IPv4Mask) {
            $this->netmask = $netmask;
        } else {
            $this->netmask = new IPv4Mask($netmask);
        }
    }

    public static function from(string $ip)
    {
        if (strpos($ip, '/') === false) {
            return new self($ip);
        } else {
            list($ip_str, $netmask) = explode('/', $ip);

            return new self($ip_str, $netmask);
        }
    }

    public function address(): string
    {
        return long2ip($this->ip_long);
    }

    public function mask(): IPv4Mask
    {
        return $this->netmask;
    }

    public function network(): IPv4Network
    {
        $subnet_mask_long = ip2long($this->mask()->subnetMask());
        $net_id_long = $this->ip_long & $subnet_mask_long;
        $net_id = long2ip($net_id_long);

        return new IPv4Network($net_id, $this->mask());
    }

    public function __toString(): string
    {
        $net = $this->address();
        $prefix = $this->mask()->prefix();

        return "$net/$prefix";
    }

    public function toInt(): int 
    {
        return $this->ip_long;
    }

    public function version(): int
    {
        return 4;
    }

    public function hostId(): string
    {
        $host_mask_long = ip2long($this->mask()->hostMask());
        $host_id_long = $this->ip_long & $host_mask_long;

        return long2ip($host_id_long);
    }

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

    public function isUnspecified(): bool
    {
        return $this->ip_long == 0;
    }

    public function toBinary(): string
    {
        $binary = Address::toBinary($this);

        return $binary;
    }

    public function toFormattedBinary($netmask, string $gap = null): string
    {
        return Address::toFormattedBinary($this, $netmask, $gap);
    }

    public function class(): string
    {
        $classes = ['A', 'B', 'C', 'D', 'E'];

        $this_ip = $this->address();

        foreach ($classes as $class) {
            if (IPv4Constants::classNetwork($class)->contains($this_ip)) {
                return $class;
            }
        }
    }

    public function isLoopback(): bool
    {
        return IPv4Constants::loopbackNetwork()->contains($this->address());
    }

    public function isLinkLocal(): bool
    {
        return IPv4Constants::linkLocalNetwork()->contains($this->address());
    }

    public function isAPIPA(): bool
    {
        return $this->isLinkLocal();
    }

    public function isMulticast(): bool
    {
        return IPv4Constants::multicastNetwork()->contains($this->address());
    }

    public function isPrivate(): bool
    {
        foreach (IPv4Constants::privateNetworks() as $private_net) {
            if ($private_net->contains($this->address())) {
                return true;
            }
        }

        return false;
    }

    public function isPublic(): bool
    {
        return !$this->isPrivate();
    }

    public function isGlobal(): bool
    {
        return $this->isPublic();
    }
}