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
    private $memCached;
    
    public function __construct($host = "localhost", $port = 11211)
    {
        $this->host = $host;
        $this->port = $port;
    }

    private function getMemcached()
    {
        if (!isset($this->memCached)) {
            $this->memCached = new \Memcached();
            //var_dump($this->memCached->getServerList());
            //echo "[{$this->host}][{$this->port}]";
            $this->memCached->addServer($this->host, $this->port);
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
        // TODO: how can this work with memcached??
        throw new \RPI\Framework\Exceptions\NotImplemented();
    }
}
