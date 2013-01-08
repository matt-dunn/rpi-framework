<?php

namespace RPI\Framework\Cache\Data;

class Store implements \RPI\Framework\Cache\Data\IStore
{
    private $provider = null;

    public function __construct(\RPI\Framework\Cache\Data\IStore $provider)
    {
        $this->provider = $provider;
    }

    public function isAvailable()
    {
        return $this->provider->isAvailable();
    }

    public function fetch($key, $autoDelete = true, &$existingCacheData = null)
    {
        return $this->provider->fetch($key, $autoDelete, $existingCacheData);
    }

    public function store($key, $value, $fileDep = null, $ttl = 0)
    {
        return $this->provider->store($key, $value, $fileDep, $ttl);
    }

    public function clear($cache_type = null, $keyPrefix = null)
    {
        return $this->provider->clear($cache_type, $keyPrefix);
    }

    public function delete($key)
    {
        return $this->provider->delete($key);
    }
}
