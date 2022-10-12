Castor Uri
==========

![php-workflow](https://github.com/castor-labs/uri/actions/workflows/php.yml/badge.svg?branch=main)
![code-coverage](https://img.shields.io/badge/Coverage-84%25-yellow.svg?longCache=true&style=flat)

RFC 3986 compliant URI value object.

## Installation

You can install the latest stable version with:

```bash
composer require castor/uri
```

## Quick Start

```php
<?php

use Castor\Net\Uri;

$uri = Uri::parse('https://example.com/hello?foo=bar');

echo $uri->getScheme(); // Prints: https
echo $uri->getHost(); // Prints: example.com
echo $uri->getPath(); // Prints: /hello
echo $uri->getRawQuery(); // Prints: foo=bar
echo $uri->getQuery()->add('foo', 'foo')->encode(); // Prints: foo=bar&foo=foo
```

To learn about the rationale behind this library, best practices when using it and implementation examples, check
the [documentation](https://castor-labs.github.io/docs/packages/uri/intro.html).