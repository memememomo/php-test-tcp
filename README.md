Test-TCP
=============

Test-TCP is testing TCP program

Original is cpan module [Test::TCP](https://metacpan.org/module/Test::TCP)

Requirements
-------------
* PHP 5.4 or later
* Sockets Support enabled (--enable-sockets)
* PCNTL Support enabled (--enable-pcntl)

Installation
-------------

you can install the script with [Composer](http://getcomposer.org/).

in your `composer.json` file:
```
{
    "require": {
        "uchiko/test-tcp": "dev-master"
    }
}
```

```
composer.phar install
```


SYNOPSIS
-------------

```
<?php

require_once 'vendor/autoload.php';

$server = new \uchiko\Test\TCP([
    "code" => function($port) { system("php -S 127.0.0.1:$port"); }
]);

$server_port = $server->port();
system("wget http://127.0.0.1:$server_port/index.html");
```

Author
-------------
uchiko <memememomo at gmail.com>

License
-------------
MIT License
