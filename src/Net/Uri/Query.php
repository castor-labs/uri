<?php

declare(strict_types=1);

/**
 * @project Castor Uri
 * @link https://github.com/castor-labs/uri
 * @project castor/uri
 * @author Matias Navarro-Carter mnavarrocarter@gmail.com
 * @license BSD-3-Clause
 * @copyright 2022 Castor Labs Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Castor\Net\Uri;

use Iterator;
use IteratorAggregate;

class Query implements IteratorAggregate
{
    /**
     * @var array<string,string[]>
     */
    private array $items;

    public function __construct()
    {
        $this->items = [];
    }

    /**
     * @param array<string,string[]> $items
     */
    public static function create(array $items = []): Query
    {
        $uri = new self();
        foreach ($items as $key => $values) {
            $uri->set($key, ...$values);
        }

        return $uri;
    }

    public static function decode(string $rawQuery): Query
    {
        $query = new self();
        $parts = explode('&', $rawQuery);
        foreach ($parts as $part) {
            $i = strpos($part, '=');
            if (!is_int($i)) {
                $query->items[urldecode($part)][] = '';

                continue;
            }

            $key = urldecode(substr($part, 0, $i));
            $value = urldecode(substr($part, $i + 1));
            $query->items[$key][] = $value;
        }

        return $query;
    }

    public function get(string $key): string
    {
        return $this->values($key)[0] ?? '';
    }

    public function values(string $key): array
    {
        return $this->items[$key] ?? [];
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function add(string $key, string $value): Query
    {
        $this->items[$key][] = $value;

        return $this;
    }

    /**
     * @param string[] $values
     */
    public function set(string $key, string ...$values): Query
    {
        $this->items[$key] = $values;

        return $this;
    }

    public function del(string $key): Query
    {
        unset($this->items[$key]);

        return $this;
    }

    public function isEmpty(): bool
    {
        return [] === $this->items;
    }

    public function toMap(): array
    {
        return iterator_to_array($this);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): Iterator
    {
        foreach ($this->items as $key => $values) {
            foreach ($values as $value) {
                yield $key => $value;
            }
        }
    }

    public function encode(): string
    {
        $parts = [];
        foreach ($this as $key => $value) {
            if ('' === $value) {
                $parts[] = urlencode($key);

                continue;
            }

            $parts[] = urlencode($key).'='.urlencode($value);
        }

        return implode('&', $parts);
    }
}
