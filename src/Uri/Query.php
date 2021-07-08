<?php

declare(strict_types=1);

/**
 * @project Castor Uri
 * @link https://github.com/castor-labs/uri
 * @package castor/uri
 * @author Matias Navarro-Carter mnavarrocarter@gmail.com
 * @license MIT
 * @copyright 2021 CastorLabs Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Castor\Net\Uri;

use Castor\Str;
use Stringable;

/**
 * Class Query parses a query string into a convenient mutable bag of data.
 *
 * Parameters and values are assumed to be already URL-decoded. Handling url
 * encoded values should be done by client code. This is mainly done because
 * the PHP CGI SAPI already decodes uris.
 *
 * This class also returns arrays when a parameter is requested. This is because
 * it supports repeated keys in the uri string.
 */
class Query implements Stringable
{
    /**
     * @psalm-param array<string,list<string>>
     */
    private array $params;

    /**
     * Query constructor.
     */
    protected function __construct()
    {
        $this->params = [];
    }

    public function __toString(): string
    {
        return $this->toStr();
    }

    /**
     * Creates an empty Query object.
     */
    public static function create(): Query
    {
        return new self();
    }

    /**
     * Makes a query string from an associative array.
     *
     * @param array<string,string> $array
     */
    public static function make(array $array): Query
    {
        $query = new self();
        foreach ($array as $key => $value) {
            $query->add($key, $value);
        }

        return $query;
    }

    /**
     * Parses a query string.
     */
    public static function parse(string $query): Query
    {
        $parsed = new self();
        if ('' === $query) {
            return $parsed;
        }
        foreach (Str\split($query, '&') as $pair) {
            $pair = Str\split($pair, '=', 2);
            $parsed->add($pair[0], $pair[1] ?? '');
        }

        return $parsed;
    }

    public function toStr(): string
    {
        $parts = [];
        foreach ($this->params as $key => $value) {
            if ('' === $key) {
                continue;
            }
            foreach ($value as $item) {
                if ('' === $item) {
                    $parts[] = $key;

                    continue;
                }
                $parts[] = $key.'='.$item;
            }
        }

        return Str\join('&', ...$parts);
    }

    public function all(): array
    {
        return $this->params;
    }

    /**
     * Gets the values of a query parameter.
     */
    public function get(string $param): array
    {
        return $this->params[$param] ?? [];
    }

    /**
     * Adds a query parameter, preserving the previous values for that parameter.
     */
    public function add(string $param, string $value): Query
    {
        $this->params[$param][] = $value;

        return $this;
    }

    /**
     * Puts values inside a parameter, overriding the previous contents of that
     * parameter.
     *
     * @param array $values
     */
    public function put(string $param, string ...$values): Query
    {
        $this->params[$param] = $values;

        return $this;
    }

    /**
     * Removes a parameter component.
     *
     * @return $this
     */
    public function del(string $param): Query
    {
        $this->params[$param] = [];

        return $this;
    }

    /**
     * Returns true if there are any values for a parameter.
     */
    public function has(string $param): bool
    {
        return ($this->params[$param] ?? []) !== [];
    }
}
