<?php

namespace Odin\IP;

class IllegalOperationException extends \Exception
{
}

class IPv4Network extends IPv4Address
{
    public function __construct(string $ip, $netmask)
    {
        parent::__construct($ip, $netmask);

        $subnet_mask_long = ip2long($this->mask()->subnetMask());
        $network_id_long = $this->ip_long & $subnet_mask_long;

        $this->ip_long = $network_id_long;
    }

    public static function from(string $ip)
    {
        if (strpos($ip, '/') === false) {
            throw new \InvalidArgumentException("'$ip' must use the slash notation to specify the netmask.");
        } else {
            list($ip_str, $netmask) = explode('/', $ip);

            return new self($ip_str, $netmask);
        }
    }

    public function hostId(): string
    {
        // because this is a net id
        return '0.0.0.0';
    }

    public function size(): int
    {
        return $this->mask()->networkSize();
    }

    public function hosts(): array
    {
        $result = [];
        for ($i = 1; $i <= $this->size(); $i++) {
            $result[] = $this->add($i);
        }

        return $result;
    }

    public function broadcast(): IPv4Address
    {
        $subnet_mask_long = ip2long($this->mask()->subnetMask());
        $broadcast_ip_long = $this->ip_long | ~$subnet_mask_long;

        $broadcast_ip = long2ip($broadcast_ip_long);

        return new IPv4Address($broadcast_ip, $this->mask());
    }

    public function firstIP(): IPv4Address
    {
        return $this->add(1);
    }

    public function lastIP(): IPv4Address
    {
        return $this->broadcast()->subtract(1);
    }

    public function contains($ip): bool
    {
        if (!$ip instanceof IPv4Address) {
            $ip = new IPv4Address($ip);
        }

        $ip_net = new self($ip->address(), $this->mask());

        return $this->address() == $ip_net->address();
    }

    public function subnetsCount($new_mask): int
    {
        if (!$new_mask instanceof IPv4Mask) {
            $new_mask = new IPv4Mask($new_mask);
        }

        $new_prefix = $new_mask->prefix();
        $current_prefix = $this->netmask->prefix();

        $prefix_diff = $new_prefix - $current_prefix;

        if ($prefix_diff < 0)
            throw new IllegalOperationException("The new mask '/$new_prefix' cannot be less than the current mask of '/$current_prefix' for this operation.");

        $num_subnets = 1 << $prefix_diff;

        return $num_subnets;
    }

    public function subnets($new_mask): array
    {
        if (!$new_mask instanceof IPv4Mask) {
            $new_mask = new IPv4Mask($new_mask);
        }

        $new_mask_prefix = $new_mask->prefix();

        $result = [];
        for ($i = 0; $i < $this->subnetsCount($new_mask); $i++) {
            $new_subnet_long = $this->ip_long + ($i << (32 - $new_mask_prefix));
            $new_subnet_ip = long2ip($new_subnet_long);

            $result[] = new IPv4Network($new_subnet_ip, $new_mask);
        }

        return $result;
    }

    public function classfulSubnetsCount(): int
    {
        $class = $this->class();
        $class_mask = IPv4Mask::fromClassDefault($class);

        $class_prefix = $class_mask->prefix();
        $current_prefix = $this->netmask->prefix();

        $prefix_diff = $current_prefix - $class_prefix;

        if ($prefix_diff < 0)
            throw new IllegalOperationException("The default mask of '/$class_prefix' cannot be greater than the current mask of '/$current_prefix' for this operation.");

        $num_subnets = 1 << $prefix_diff;

        return $num_subnets;
    }

    public function classfulSubnets(): array
    {
        $class = $this->class();
        $class_mask = IPv4Mask::fromClassDefault($class);
        $current_net = $this->address();

        $class_net = new IPv4Network($current_net, $class_mask);
        $current_mask = $this->mask();

        return $class_net->subnets($current_mask);
    }
}