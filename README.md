<img src="https://static.germania-kg.com/logos/ga-logo-2016-web.svgz" width="250px">

------



# Germania KG · RequestHandler for Twig



## Installation

```bash
$ composer require germania-kg/twig-requesthandler
```



## Setup

The constructor accepts a **Twig Environment** and **PSR-17 ResponseFactory**. This example uses Tobias Nyholm's [nyholm/psr7](https://packagist.org/packages/nyholm/psr7) package: `composer require nyholm/psr7`

```php
<?php

use Germania\TwigRequestHandler\TwigRequestHandler;  
use Twig\Environment as Twig;
use Nyholm\Psr7\Factory\Psr17Factory;

// Dependencies
$twig = new Twig( ... );
$psr17Factory = new Psr17Factory;

// Instantiation
$request_handler = new TwigRequestHandler($twig, $psr17Factory);
```



## Usage

Have a **ServerRequest** at hand and configure it with a *template* attribute and a *context* attribute. 

- The *template* attribute must be a *string* as required by Twig.
- The *context* attribute must be an *array* as required by Twig; 
  Instances of *ArrayObject* will be converted.

**N.B.** Invalid variable types will lead to a *RuntimeException* at runtime on request handling, not during configuration!

```php
<?php
$request = $psr17Factory->createServerRequest('GET', 'http://tnyholm.se');

$request = $request
  ->withAttribute('template', 'website.twig')
  ->withAttribute('context', [
    'title' => 'The Website title',
    'company' => 'ACME corp.'
  ]);
```

Now, the above RequestHandler can be used as normal:

```php
$response = $request_handler->handle($request);

echo $response->getBody()->__toString();
// s.th. like 
// "<title>The Website title · ACME corp.</title>"
```



## Configuration

You can change these default settings:

```php
$request_handler->setTwig($twig);
$request_handler->setResponseFactory($another);
```

You can change these default settings

```php
$request_handler->setTemplateAttributeName("template")
						    ->setContextAttributeName("context")
                ->setResponseContentType("text/html") 
                ->setResponseStatusCode(200);
```

… and the core components:

```php
$request_handler->setTwig($twig)
                ->setResponseFactory($another);
```



## Development

Grab and go using one of these:

```bash
$ git clone git@github.com:GermaniaKG/TwigRequestHandler.git
$ gh repo clone GermaniaKG/TwigRequestHandler
```

