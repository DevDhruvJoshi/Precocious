<?php
namespace System\App;
class Cache {

    private $cacheBackend; // Variable to hold the cache backend instance

    /**
     * Constructor to initialize the cache backend
     *
     * @param string $backendType Type of cache backend to use (e.g., 'apc', 'memcached', 'redis')
     * @param array $options Options for the cache backend (e.g., server address, port)
     */
    public function __construct() {
        switch ($backendType = env('Cache_Type')) {
            case 'apc':
                // No connection required for APC
                break;
            case 'memcached':
                !extension_loaded('memcached') ? throw new Exc("memcached extension is not installed https://pecl.php.net/package/memcached") : '';
                $this->cacheBackend = new Memcache();
                $this->cacheBackend->connect(env('Cache_Host'), env('Cache_Port'));
                break;
            case 'redis':
                $this->cacheBackend = new Redis();
                $this->cacheBackend->connect(env('Cache_Host'), env('Cache_Port'));
                break;
            default:
                throw new SystemExc("Invalid cache backend: $backendType");
        }
    }

    /**
     * Set a value in the cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to store
     * @param int $ttl Time-to-live in seconds (optional, default is 3600)
     */
    public function set($key, $value, $ttl = 3600) {
        $this->cacheBackend->set($key, $value, $ttl);
    }

    /**
     * Get a value from the cache
     *
     * @param string $key Cache key
     * @return mixed Cached value or null if not found
     */
    public function get($key = 'MyName') {
        return $this->cacheBackend->get($key);
    }

    /**
     * Check if a key exists in the cache
     *
     * @param string $key Cache key
     * @return bool True if key exists, false otherwise
     */
    public function has($key) {
        return $this->cacheBackend->get($key) !== false;
    }

    /**
     * Delete a value from the cache
     *
     * @param string $key Cache key
     */
    public function delete($key) {
        $this->cacheBackend->delete($key);
    }

    /**
     * Clear the entire cache
     */
    public function clear() {
        switch ($this->cacheBackend->getType()) {
            case 'apc':
                apc_clear_cache();
                break;
            case 'memcached':
                $this->cacheBackend->flush();
                break;
            case 'redis':
                $this->cacheBackend->flushAll();
                break;
        }
    }
}
