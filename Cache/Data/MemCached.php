<?php

namespace RPI\Framework\Cache\Data;

/**
 * MemCached cache support wrapper
 * Supports file dependencies and time to live on cached data
 *
 * @author Matt Dunn
 */
class MemCached implements \RPI\Framework\Cache\IData
{
    private $isAvailable = null;
    
    /**
     *
     * @var \Memcached
     */
    private $memCached;
    
    private $host = null;
    private $port = null;
    private $persistentId = null;
    
    public function __construct($host = null, $port = null, $persistentId = null)
    {
        $this->host = (isset($host) ? $host : "localhost");
        $this->port = (isset($port) ? $port : 11211);
        $this->persistentId = (isset($persistentId) ? $persistentId : "default_pool:".get_class());
    }

    private function getMemcached()
    {
        if (!isset($this->memCached)) {
            $this->memCached = new \Memcached($this->persistentId);

            if(count($this->memCached->getServerList()) == 0) {
                $this->memCached->setOption(\Memcached::OPT_RECV_TIMEOUT, 1000);
                $this->memCached->setOption(\Memcached::OPT_SEND_TIMEOUT, 1000);
                $this->memCached->setOption(\Memcached::OPT_TCP_NODELAY, true);
                $this->memCached->setOption(\Memcached::OPT_SERVER_FAILURE_LIMIT, 50);
                $this->memCached->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 500);
                $this->memCached->setOption(\Memcached::OPT_RETRY_TIMEOUT, 300);
                //$this->memCached->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
                $this->memCached->setOption(\Memcached::OPT_REMOVE_FAILED_SERVERS, true);
                $this->memCached->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

                $this->memCached->addServer($this->host, $this->port);
                
                $stats = $this->memCached->getStats();
                var_dump($stats);
                if (isset($stats, $stats[$this->host.":".$this->port]) && $stats[$this->host.":".$this->port]["version"] == "") {
                    throw new \RPI\Framework\Exceptions\Exception("Memcached server not found '".$this->host.":".$this->port."'");
                }
            }
        }

        return $this->memCached;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        if ($this->isAvailable === null) {
            $this->isAvailable = extension_loaded("Memcached");
        }

        return $this->isAvailable;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key, $autoDelete = true, &$existingCacheData = null)
    {
        if ($this->isAvailable() === true) {
            $data = $this->getMemcached()->get($key);
            if ($data === false) {
                return false;
            }

            $existingCacheData = $data["value"];

            if (isset($data["fileDep"]) && isset($data["fileDep_mod"])) {
                if (is_array($data["fileDep"])) {
                    $fileCount = count($data["fileDep"]);
                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($data["fileDep_mod"][$i] != filemtime($data["fileDep"][$i])) {
                            if ($autoDelete) {
                                $this->delete($key);
                            }

                            return false;
                        }
                    }
                } else {
                    if ($data["fileDep_mod"] != filemtime($data["fileDep"])) {
                        if ($autoDelete) {
                            $this->delete($key);
                        }

                        return false;
                    }
                }
            }

            return $data["value"];
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function store($key, $value, $fileDep = null, $ttl = 0)
    {
        if ($this->isAvailable() === true) {
            if (is_array($fileDep)) {
                $fileDepMod = array();
                foreach ($fileDep as $file) {
                    $fileDepMod[] = filemtime($file);
                }
                
                $this->getMemcached()->set(
                    $key,
                    array("value" => $value, "fileDep" => $fileDep, "fileDep_mod" => $fileDepMod),
                    $ttl
                );
            } else {
                $this->getMemcached()->set(
                    $key,
                    array("value" => $value, "fileDep" => $fileDep, "fileDep_mod" => filemtime($fileDep)),
                    $ttl
                );
            }
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if ($this->isAvailable() === true) {
            return $this->getMemcached()->flush(0);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        if ($this->isAvailable() === true) {
            return $this->getMemcached()->delete($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deletePattern($pattern)
    {
        $keys = new \RegexIterator(
            new \ArrayIterator($this->getMemcached()->getAllKeys()),
            $pattern,
            \RegexIterator::MATCH
        ); 

        foreach ($keys as $key) {
            $this->delete($key);
        }
    }
}
