<?php

namespace Odin\IP;

class InvalidAddressException extends \Exception
{
}

class InvalidNetworkException extends \Exception
{
}

class InvalidNetmaskException extends \Exception
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
                throw new InvalidAddressException("Unexpected '/' found in '$ip'", 1);
            } else {
                throw new InvalidAddressException("'$ip' is in an unexpected format", 0);
            }
        }

        $this->ip = ip2long($ip);
    }

    public function address()
    {
        return long2ip($this->ip);
    }
}