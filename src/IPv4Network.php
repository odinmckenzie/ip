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
}