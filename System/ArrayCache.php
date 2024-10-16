<?php

namespace System;

class ArrayCache
{
    protected $cache = [];
    protected $expiration = [];

    public function get(string $key)
    {
        if (isset($this->expiration[$key]) && $this->expiration[$key] < time()) {
            unset($this->cache[$key], $this->expiration[$key]); // Remove expired item
            return null;
        }
        return $this->cache[$key] ?? null;
    }

    public function set(string $key, $value, $ttl = 0)
    {
        $this->cache[$key] = $value;
        if ($ttl > 0) {
            $this->expiration[$key] = time() + $ttl; // Set expiration time
        }
    }

    public function clear()
    {
        $this->cache = [];
        $this->expiration = [];
    }
}
