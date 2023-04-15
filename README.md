# Observability library for PHP



## Getting started

Getting started is usually easy: just follow the instructions below.


### Installation

Use [Composer](https://getcomposer.org/) to install this library into your project:

```shell
composer require 0to10/observability-php
```


### Basic usage

```php
<?php

require 'vendor/autoload.php';

use ZERO2TEN\Observability\Client;

// Creates a Client instance with the first supported AgentInterface
// implementation configured in the Client class
$client = Client::create();

// Alternatively, you can instantiate a Client instance yourself
$nullAgent = new NullAgent();
$client = new Client($nullAgent);
```



### Legacy usage

Library versions up to 2.0.0 continue to support using the `Client` class located
in the `Nouve\APM` namespace. Please do not use this method for new projects.

```php
<?php

require 'vendor/autoload.php';

use Nouve\APM\Agents\NullAgent;
use Nouve\APM\Client;

// Creates a Client instance with the first supported AgentInterface
// implementation configured in the Client class
$client = Client::create();

// Alternatively, you can instantiate a Client instance yourself
$nullAgent = new NullAgent();
$client = new Client($nullAgent);
```

