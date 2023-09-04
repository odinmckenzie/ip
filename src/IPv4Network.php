<?php

namespace Odin\IP;

class IPv4Network extends IPv4Address
{
    private $netmask;

    public function __construct($ip, $netmask)
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

    public function hostId($mask=null): string
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

    public function contains($ip): bool
    {
        if (!$ip instanceof IPv4Address) {
            $ip = new IPv4Address($ip);
        }

        $net = $ip->network($this->mask());

        return $this->address() == $net->address();
    }
}