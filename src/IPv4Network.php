<?php

namespace Odin\IP;

/**
 * Exception class for illegal network operations.
 */
class IllegalOperationException extends \Exception
{
}

/**
 * Represents an IPv4 network and provides various operations related to it.
 */
class IPv4Network extends IPv4Address
{
    /**
     * Constructs an IPv4Network object based on the provided IP address and netmask.
     *
     * @param string $ip The IP address in string format.
     * @param mixed $netmask The subnet mask (string or integer) or a prefix length (integer).
     */
    public function __construct(string $ip, $netmask)
    {
        parent::__construct($ip, $netmask);

        $subnet_mask_long = ip2long($this->mask()->subnetMask());
        $network_id_long = $this->ip_long& $subnet_mask_long;

        $this->ip_long = $network_id_long;
    }

    /**
     * Create an IPv4Network object from an IP address in slash notation.
     *
     * @param string $ip The IP address in slash notation (e.g., "192.168.1.0/24").
     *
     * @return IPv4Network An IPv4Network object created from the slash notation.
     *
     * @throws InvalidArgumentException If the slash notation is invalid.
     */
    public static function from(string $ip)
    {
        if (strpos($ip, '/') === false) {
            throw new \InvalidArgumentException("'$ip' must use the slash notation to specify the netmask.");
        } else {
            list($ip_str, $netmask) = explode('/', $ip);

            return new self($ip_str, $netmask);
        }
    }

    /**
     * Get the host ID (always "0.0.0.0" for network IDs).
     *
     * @return string The host ID as "0.0.0.0".
     */
    public function hostId(): string
    {
        // because this is a net id
        return '0.0.0.0';
    }

    /**
     * Get the size of the network represented by the subnet mask.
     *
     * @return int The size of the network.
     */
    public function size(): int
    {
        return $this->mask()->networkSize();
    }

    /**
     * Get an array of IPv4Address objects representing all hosts within the network.
     *
     * @return array An array of IPv4Address objects.
     */
    public function hosts(): array
    {
        $result = [];
        for ($i = 1; $i <= $this->size(); $i++) {
            $result[] = $this->add($i);
        }

        return $result;
    }

    /**
     * Get the broadcast address as an IPv4Address object.
     *
     * @return IPv4Address The broadcast address.
     */
    public function broadcast(): IPv4Address
    {
        $subnet_mask_long = ip2long($this->mask()->subnetMask());
        $broadcast_ip_long = $this->ip_long | ~$subnet_mask_long;

        $broadcast_ip = long2ip($broadcast_ip_long);

        return new IPv4Address($broadcast_ip, $this->mask());
    }

    /**
     * Get the first usable IP address as an IPv4Address object.
     *
     * @return IPv4Address The first usable IP address.
     */
    public function firstIP(): IPv4Address
    {
        return $this->add(1);
    }

    /**
     * Get the last usable IP address as an IPv4Address object.
     *
     * @return IPv4Address The last usable IP address.
     */
    public function lastIP(): IPv4Address
    {
        return $this->broadcast()->subtract(1);
    }

    /**
     * Check if the network contains a given IP address or IPv4Address object.
     *
     * @param mixed $ip The IP address or IPv4Address object to check.
     *
     * @return bool True if the network contains the provided IP, false otherwise.
     */
    public function contains($ip): bool
    {
        if (!$ip instanceof IPv4Address) {
            $ip = new IPv4Address($ip);
        }

        $ip_net = new self($ip->address(), $this->mask());

        return $this->address() == $ip_net->address();
    }

    /**
     * Calculate the number of subnets that can be created by applying a new subnet mask.
     *
     * @param mixed $new_mask The new subnet mask (string or IPv4Mask object).
     *
     * @return int The number of subnets that can be created.
     *
     * @throws IllegalOperationException If the new mask is invalid or reduces the network size.
     */
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

    /**
     * Create an array of IPv4Network objects representing subnets with a new subnet mask.
     *
     * @param mixed $new_mask The new subnet mask (string or IPv4Mask object).
     *
     * @return array An array of IPv4Network objects representing subnets.
     *
     * @throws IllegalOperationException If the new mask is invalid or reduces the network size.
     */
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

    /**
     * Calculate the number of classful subnets that can be created from the current network.
     *
     * @return int The number of classful subnets that can be created.
     *
     * @throws IllegalOperationException If the default mask cannot be applied to the current mask.
     */
    public function classfulSubnetsCount(): int
    {
        $class = $this->class();
        $class_mask = IPv4Mask::fromClass($class);

        $class_prefix = $class_mask->prefix();
        $current_prefix = $this->netmask->prefix();

        $prefix_diff = $current_prefix - $class_prefix;

        if ($prefix_diff < 0)
            throw new IllegalOperationException("The default mask of '/$class_prefix' cannot be greater than the current mask of '/$current_prefix' for this operation.");

        $num_subnets = 1 << $prefix_diff;

        return $num_subnets;
    }

    /**
     * Create an array of IPv4Network objects representing classful subnets.
     *
     * @return array An array of IPv4Network objects representing classful subnets.
     *
     * @throws IllegalOperationException If the default mask cannot be applied to the current mask.
     */
    public function classfulSubnets(): array
    {
        $class = $this->class();
        $class_mask = IPv4Mask::fromClass($class);
        $current_net = $this->address();

        $class_net = new IPv4Network($current_net, $class_mask);
        $current_mask = $this->mask();

        return $class_net->subnets($current_mask);
    }
}