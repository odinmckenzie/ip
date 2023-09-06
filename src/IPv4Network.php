<?php

namespace Odin\IP;

class IllegalOperationException extends \Exception
{
}

class IPv4Network extends IPv4Address
{
    private $netmask;

    public function __construct(string $ip, $netmask)
    {
        parent::__construct($ip);

        if ($netmask instanceof IPv4Mask) {
            $this->netmask = $netmask;
        } else {
            $this->netmask = new IPv4Mask($netmask);
        }

        $subnet_mask_long = ip2long($this->netmask->subnetMask());
        $network_id_long = $this->ip & $subnet_mask_long;

        $network_id = long2ip($network_id_long);

        parent::__construct($network_id);
    }

    public static function from(string $ip): self
    {
        if (strpos($ip, '/') == false)
            throw new \InvalidArgumentException("'$ip' must use the / notation.");

        list($ip_str, $netmask) = explode('/', $ip);

        return new self($ip_str, $netmask);
    }

    public function hostId($mask = null): string
    {
        return '0.0.0.0';
    }

    public function mask(): IPv4Mask
    {
        return $this->netmask;
    }

    public function size(): int
    {
        return $this->netmask->networkSize();
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
        $subnet_mask_long = ip2long($this->netmask->subnetMask());
        $broadcast_ip_long = $this->ip | ~$subnet_mask_long;

        $broadcast_ip = long2ip($broadcast_ip_long);

        return new IPv4Address($broadcast_ip);
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

        $net = $ip->network($this->mask());

        return $this->address() == $net->address();
    }

    public function __toString(): string
    {
        $net = $this->address();
        $prefix = $this->mask()->prefix();

        return "$net/$prefix";
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
            $new_subnet_long = $this->ip + ($i << (32 - $new_mask_prefix));
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