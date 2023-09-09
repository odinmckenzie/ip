# Usage Guide for the IP Library

This document aims to describe the usage and features of the `Address`, `IPv4Address`, `Network` and `IPv4Network` classes in the `Odin\IP` namespace. These classes provide a variety of functionalities to manage and manipulate IPv4 addresses.

## Creating an `IPv4Address` Object

### Using `Address::from()`

You can create an `IPv4Address` object using the static method `from()` in the `Address` class. Here are some examples:

```php
use Odin\IP\Address;

$ip = Address::from('192.168.1.2/24');
$ip = Address::from('192.168.1.2');     // uses a default prefix of /32
```

#### With Subnet Mask or Host Mask

You can also specify a subnet mask or a host mask as follows:

```php
use Odin\IP\Address;

$ip = Address::from('192.168.1.2/255.255.255.0');  // using subnet mask
$ip = Address::from('192.168.1.2/0.0.0.255');      // using host mask
```

### Using Second Argument in `Address::from()`

The `from()` method can accept a second argument to specify the netmask in various formats:

```php
use Odin\IP\Address;

$ip = Address::from('192.168.1.2', '/24');
$ip = Address::from('192.168.1.2', '24');
$ip = Address::from('192.168.1.2', 24);
$ip = Address::from('192.168.1.2', '255.255.255.0');

// the host mask of 0.0.0.255 is the inverse 
// of a the subnet mask 255.255.255.0
$ip = Address::from('192.168.1.2', '0.0.0.255');
```

### Illegal Usage of `Address::from()`

The slash notation and the second argument cannot be use together in the `from()` method. The following example will throw an InvalidAddressException:

```php
use Odin\IP\Address;

// throws InvalidAddressException
$ip = Address::from('192.168.1.2/24', '255.255.255.0');
```

## Getting Address Information

### Basic Information

Here's how you can get the IP address, subnet mask, and other properties:

```php
use Odin\IP\Address;

$ip = Address::from('192.168.1.2/24');

$address    = $ip->address();               // 192.168.1.2
$ip_str     = (string) $ip;                 // 192.168.1.2/24
$prefix     = $ip->mask()->prefix();        // 24
$netid_str  = $ip->network()->address();    // 192.168.1.0
$hostid_str = $ip->hostId();                // 0.0.0.2
```

### Address Class and Types

You can find the address class (A, B, C, D, or E) and check for specific address types:

```php
use Odin\IP\Address;

$ip = Address::from('192.168.1.2/24');

$class      = $ip->class();         // C
$private    = $ip->isPrivate();     // true
$loopback   = $ip->isLoopback();    // false
$linkLocal  = $ip->isLinkLocal();   // false
$multicast  = $ip->isMulticast();   // false
$pubic      = $ip->isPublic();      // false
```

### Binary Representation

```php
use Odin\IP\Address;

$ip = Address::from('192.168.1.2/24');

$binary                 = $ip->toBinary();                      // 11000000101010000000000100000010
$fmt_binary             = $ip->toFormattedBinary();             // 11000000.10101000.00000001.00000010
$fmt_binary_with_gap    = $ip->toFormattedBinary($gap=' ');     // 11000000.10101000.00000001. 00000010
$fmt_binary_with_gap2   = $ip->toFormattedBinary($gap='  ');    // 11000000.10101000.00000001.  00000010
```

### Arithmetic Operations

You can perform arithmetic operations like adding or subtracting an integer value from the IP address:

```php
use Odin\IP\Address;

$ip = Address::from('192.168.1.2/24');

$next_ip        = $ip->add(1);          // new IPv4Address('192.168.1.3', '/24')
$previous_ip    = $ip->subtract(1);     // new IPv4Address('192.168.1.1', '/24')
```

### Other Ways to Create an `IPv4Address` Object

Apart from the `Address::from()` method, you can also create an `IPv4Address` object directly using its constructor. Here are some examples:

```php
use Odin\IP\IPv4Address;

$ip = new IPv4Address('192.168.1.2');                   // Default netmask of /32 is used
$ip = new IPv4Address('192.168.1.2', 24);               // With prefix length
$ip = new IPv4Address('192.168.1.2', '24');             // With prefix length
$ip = new IPv4Address('192.168.1.2', '/24');            // With prefix length
$ip = new IPv4Address('192.168.1.2', '255.255.255.0');  // With subnet mask
$ip = new IPv4Address('192.168.1.2', '0.0.0.255');      // With host mask
```


## Creating an `IPv4Network` Object

### Using `Network::from()`

Create an `IPv4Network` object using the static method `from()` from the `Network` class:

```php
use Odin\IP\Network;

$net = Network::from('192.168.1.0/24');
$net = Network::from('192.168.1.0/255.255.255.0');
$net = Network::from('192.168.1.0/0.0.0.255');
```

You can also specify a subnet mask as a separate parameter:

```php
use Odin\IP\Network;

$net = Network::from('192.168.1.0', '/24');
$net = Network::from('192.168.1.0', '24');
$net = Network::from('192.168.1.0', 24);
$net = Network::from('192.168.1.0', '255.255.255.0');
$net = Network::from('192.168.1.0', '0.0.0.255');
```

### From an IPv4Address Object

You can also create an `IPv4Network` object from an existing `IPv4Address` object:

```php
use Odin\IP\Network;

$ip = Address::from('192.168.1.2/24');
$net = $ip->network();  // Creates a new IPv4Network('192.168.1.0', '/24')
```

While the `IPv4Network` class inherits all methods from its superclass `IPv4Address`, it also introduces a set of additional methods tailored specifically for network operations. Below are some of these additional functionalities:


## Network Information

Here's how you can access various properties of a network:

### Basic Network Information

```php
use Odin\IP\Network;

$net = Network::from('192.168.1.0/24');

$address    = $net->address();   // 192.168.1.0
$net_str    = (string) $net;     // 192.168.1.0/24
$hostid_str = $net->hostId();    // 0.0.0.0
```

### Network Size and Range

```php
use Odin\IP\Network;

$net = Network::from('192.168.1.0/24');

$size       = $net->size();      // 254
$first_ip   = $net->firstIP();   // new IPv4Address('192.168.1.1', '/24')
$last_ip    = $net->lastIP();    // new IPv4Address('192.168.1.254', '/24')
```

### Broadcast Address

```php
use Odin\IP\Network;

$net = Network::from('192.168.1.0/24');

$broadcast  = $net->broadcast(); // new IPv4Address('192.168.1.255', '/24')
```

### Host Addresses

```php
use Odin\IP\Network;

$net = Network::from('192.168.1.0/24');

// Returns an array of IPv4Address objects for each host in the network.
$hosts = $net->hosts();
```

### Checking if an Address is in the Network

```php
use Odin\IP\Network;

$net = Network::from('192.168.1.0/24');

$result = $net->contains('192.168.1.5');  // true
```

### Finding Subnets

You can also get an array of subnets within the network:

```php
use Odin\IP\Network;

$net = Network::from('192.168.1.0/24');

$subnets = $net->subnets('/26'); // Returns an array of IPv4Network objects
```

