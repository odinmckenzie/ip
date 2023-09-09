<?php

namespace Odin\IP;

class Network
{
    /**
     * Creates an IPv4Network object using either the constructor or from() method of IPv4Network.
     *
     * @param string $ip The IPv4 address as a string.
     * @param IPv4Mask|string  $netmask The network mask (default is '/32' if omitted).
     *
     * @return IPv4Network The IPv4Network instance.
     *
     * @throws InvalidAddressException If the provided IP address is invalid.
     * @throws \InvalidArgumentException If the slash notation is invalid.
     *
     * @see IPv4Network
     */
    public static function from(string $ip, $netmask = null)
    {
        if ($netmask === null) {
            return IPv4Network::from($ip);
        } else {
            return new IPv4Network($ip, $netmask);
        }
    }

    /**
     * Summarizes an array of IPv4 networks into a single supernet.
     *
     * This method takes an array of IPv4Network objects or valid network
     * representations and calculates the smallest supernet that encompasses all
     * provided networks.
     *
     * @param array $networks An array of IPv4Network objects or network
     *                        representations (e.g., "192.168.1.0/24").
     *
     * @return IPv4Network A new IPv4Network object representing the summarized supernet.
     *
     * @throws InvalidArgumentException If an invalid network representation is provided.
     *
     * @see IPv4Network
     */
    public static function summarize(array $networks): IPv4Network
    {
        $lowest_ip_long = PHP_INT_MAX;
        $highest_ip_long = 0;

        foreach ($networks as $network) {
            if ($network instanceof IPv4Network) {
                $ip_long = $network->toInt();
                $mask_bits = $network->mask()->prefix();
            } else {
                $net = IPv4Network::from($network);

                $ip_long = $net->toInt();
                $mask_bits = $net->mask()->prefix();
            }

            $mask_decimal = -1 << (32 - $mask_bits);

            $network_long = $ip_long & $mask_decimal;

            $lowest_ip_long = min($lowest_ip_long, $network_long);
            $highest_ip_long = max($highest_ip_long, $network_long);
        }

        $mask_bits = 32;
        $mask_long = -1 << (32 - $mask_bits);
        while (($lowest_ip_long & $mask_long) != ($highest_ip_long & $mask_long)) {
            $mask_bits--;
            $mask_long = -1 << (32 - $mask_bits);
        }

        $supernet_ip_long = $lowest_ip_long & $mask_long;
        $supernet_ip = long2ip($supernet_ip_long);

        return new IPv4Network($supernet_ip, $mask_bits);
    }
}
