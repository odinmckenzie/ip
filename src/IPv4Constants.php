<?php

namespace Odin\IP;

/**
 * Provides constants and methods for working with various IPv4 network constants.
 */
class IPv4Constants
{
    /**
     * Get the first four(4) bits corresponding to a specified IPv4 address class.
     *
     * @param string $class The IP address class ('A', 'B', 'C', 'D', or 'E').
     *
     * @return string The first four(4) bits corresponding to a specified IPv4 address class.
     *
     * @throws InvalidArgumentException If an invalid IPv4 address class is provided.
     */
    public static function classBits(string $class): string
    {
        $classes = [
            'A' => '0000',
            'B' => '1000',
            'C' => '1100',
            'D' => '1110',
            'E' => '1111',
        ];

        $class = strtoupper($class);

        if (!array_key_exists($class, $classes)) {
            throw new \InvalidArgumentException("Invalid class: '$class' must be either 'A', 'B', 'C', 'D' or 'E'.");
        }

        return $classes[$class];
    }

    /**
     * Get the prefix corresponding to a specified IPv4 address class.
     *
     * @param string $class The IP address class ('A', 'B' or 'C').
     *
     * @return string The prefix corresponding to a specified IPv4 address class.
     *
     * @throws InvalidArgumentException If an invalid IPv4 address class is provided.
     */
    public static function classPrefix(string $class): int
    {
        $classes = [
            'A' => 8,
            'B' => 16,
            'C' => 24,
        ];

        $class = strtoupper($class);

        if (!array_key_exists($class, $classes)) {
            throw new \InvalidArgumentException("Invalid class: '$class' must be either 'A', 'B' or 'C'.");
        }

        return $classes[$class];
    }

    /**
     * Get the IPv4 network object corresponding to a specified IPv4 address class.
     *
     * @param string $class The IP address class ('A', 'B', 'C', 'D', or 'E').
     *
     * @return IPv4Network The IPv4 network object representing the specified class.
     *
     * @throws InvalidArgumentException If an invalid IP address class is provided.
     */
    public static function classNetwork(string $class): IPv4Network
    {
        $classes = [
            'A' => '0.0.0.0/1',
            'B' => '128.0.0.0/2',
            'C' => '192.0.0.0/3',
            'D' => '224.0.0.0/4',
            'E' => '240.0.0.0/4',
        ];

        $class = strtoupper($class);

        if (!array_key_exists($class, $classes)) {
            throw new \InvalidArgumentException("Invalid class: '$class' must be either 'A', 'B', 'C', 'D' or 'E'.");
        }

        $classNetwork = $classes[$class];

        return IPv4Network::from($classNetwork);
    }

    /**
     * Get the IPv4 multicast network object.
     *
     * @return IPv4Network The IPv4 multicast network object.
     */
    public static function multicastNetwork(): IPv4Network
    {
        return self::classNetwork('D');
    }

    /**
     * Get the IPv4 reserved network object.
     *
     * @return IPv4Network The IPv4 reserved network object.
     */
    public static function reservedNetwork(): IPv4Network
    {
        return self::classNetwork('E');
    }

    /**
     * Get an array of IPv4 network objects representing various private networks.
     *
     * @return array An array of IPv4 network objects representing private networks.
     */
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

    /**
     * Get the IPv4 link-local network object.
     *
     * @return IPv4Network The IPv4 link-local network object.
     */
    public static function linkLocalNetwork(): IPv4Network
    {
        return IPv4Network::from('169.254.0.0/16');
    }

    /**
     * Get the IPv4 Automatic Private IP Addressing (APIPA) network object.
     *
     * @return IPv4Network The IPv4 APIPA network object.
     */
    public static function apipaNetwork(): IPv4Network
    {
        return self::linkLocalNetwork();
    }

    /**
     * Get the IPv4 loopback network object.
     *
     * @return IPv4Network The IPv4 loopback network object.
     */
    public static function loopbackNetwork(): IPv4Network
    {
        return IPv4Network::from('127.0.0.0/8');
    }
}
