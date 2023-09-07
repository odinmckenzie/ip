<?php

namespace Odin\IP;

class IPv4Constants
{
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
}