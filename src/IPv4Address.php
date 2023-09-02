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
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (strpos($ip, '/') !== false) {
                throw new InvalidAddressException("Unexpected '/' found in $ip.");
            } else {
                throw new InvalidAddressException("$ip is in an unexpected format.");
            }
        }

        $this->ip = ip2long($ip);
    }

    public function address()
    {
        return long2ip($this->ip);
    }
}