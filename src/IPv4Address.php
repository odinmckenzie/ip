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

    public static function linkLocalNetwork(): IPv4Network
    {
        return IPv4Network::from('169.254.0.0/16');
    }

    public static function apipaNetwork(): IPv4Network
    {
        return self::linkLocalNetwork();
    }

    public static function loopbackNetwork(): IPv4Network
    {
        return IPv4Network::from('127.0.0.0/8');
    }

    public function class(): string
    {
        $classes = ['A', 'B', 'C', 'D', 'E'];

        $this_ip = $this->address();

        foreach ($classes as $class) {
            if ($this->classNetwork($class)->contains($this_ip)) {
                return $class;
            }
        }
    }

    public static function classNetwork(string $class): IPv4Network
    {
        $classes = [
            'A' => IPv4Network::from('0.0.0.0/1'),
            'B' => IPv4Network::from('128.0.0.0/2'),
            'C' => IPv4Network::from('192.0.0.0/3'),
            'D' => IPv4Network::from('224.0.0.0/4'),
            'E' => IPv4Network::from('240.0.0.0/4'),
        ];

        $class = strtoupper($class);

        if (!array_key_exists($class, $classes)) {
            throw new \InvalidArgumentException("'$class' must be either 'A', 'B', 'C', 'D' or 'E'.");
        }

        return $classes[$class];
    }

    public static function multicastNetwork(): IPv4Network
    {
        return self::classNetwork('D');
    }

    public static function reservedNetwork(): IPv4Network
    {
        return self::classNetwork('E');
    }

    public static function privateNetworks(): array
    {
        return [
            IPv4Network::from('0.0.0.0/8'),
            IPv4Network::from('10.0.0.0/8'),
            IPv4Network::from('127.0.0.0/8'),
            IPv4Network::from('169.254.0.0/16'),
            IPv4Network::from('172.16.0.0/12'),
            IPv4Network::from('192.0.0.0/29'),
            IPv4Network::from('192.0.0.170/31'),
            IPv4Network::from('192.0.2.0/24'),
            IPv4Network::from('192.168.0.0/16'),
            IPv4Network::from('198.18.0.0/15'),
            IPv4Network::from('198.51.100.0/24'),
            IPv4Network::from('203.0.113.0/24'),

            // includes 255.255.255.255
            IPv4Network::from('240.0.0.0/4'),
        ];
    }

    public function isLoopback(): bool
    {
        return self::loopbackNetwork()->contains($this->address());
    }

    public function isLinkLocal(): bool
    {
        return self::linkLocalNetwork()->contains($this->address());
    }

    public function isAPIPA(): bool
    {
        return $this->isLinkLocal();
    }

    public function isMulticast(): bool
    {
        return self::multicastNetwork()->contains($this->address());
    }

    public function isPrivate(): bool
    {
        foreach (self::privateNetworks() as $private_net) {
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