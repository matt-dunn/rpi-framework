<?php

namespace RPI\Framework\Cache\Data;

/**
 * APC cache support wrapper
 * Supports file dependencies and time to live on cached data
 *
 * @author Matt Dunn
 */
class Apc implements \RPI\Framework\Cache\IData
{
    private $data = array();

    /**
     * {@inheritdoc}
     */
    public function fetch($key, $autoDelete = true, &$existingCacheData = null)
    {
        $data = $this->getItem($key);
        if ($data === false) {
            return false;
        }

        $existingCacheData = $data["value"];

        if (isset($data["fileDep"]) && isset($data["fileDep_mod"])) {
            if (is_array($data["fileDep"])) {
                $fileCount = count($data["fileDep"]);
                for ($i = 0; $i < $fileCount; $i++) {
                    if (filemtime($data["fileDep"][$i]) > $data["fileDep_mod"][$i]) {
                        if ($autoDelete && function_exists("apc_delete")) {
                            $this->delete($key);
                        }

                        return false;
                    }
                }
            } else {
                if (filemtime($data["fileDep"]) > $data["fileDep_mod"]) {
                    if ($autoDelete && function_exists("apc_delete")) {
                        $this->delete($key);
                    }

                    return false;
                }
            }
        }

        return $data["value"];
    }

    /**
     * {@inheritdoc}
     */
    public function store($key, $value, $fileDep = null, $ttl = 0)
    {
        if (is_array($fileDep)) {
            $fileDepMod = array();
            foreach ($fileDep as $file) {
                $fileDepMod[] = filemtime($file);
            }

            $data = array(
                "value" => $value,
                "modified" => microtime(true),
                "fileDep" => $fileDep,
                "fileDep_mod" => $fileDepMod
            );

            $this->data[$key] = $data;

            return apc_store(
                $key,
                $data,
                $ttl
            );
        } else {
            $data = array(
                "value" => $value,
                "modified" => microtime(true),
                "fileDep" => $fileDep,
                "fileDep_mod" => filemtime($fileDep)
            );

            $this->data[$key] = $data;

            return apc_store(
                $key,
                $data,
                $ttl
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        unset($this->data);
        $this->data = array();
        return apc_clear_cache("user");
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        unset($this->data[$key]);
        return apc_delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deletePattern($pattern)
    {
        $deleteCount = 0;
        foreach (new \APCIterator("user", $pattern) as $counter) {
            if ($this->delete($counter["key"]) !== false) {
                unset($this->data["key"]);
                $deleteCount++;
            }
        }

        return $deleteCount;
    }

    public function getItemModifiedTime($key)
    {
        $data = $this->getItem($key);
        if ($data === false) {
            return false;
        }

        if (isset($data["modified"])) {
            return (double)$data["modified"];
        }
        
        return false;
    }
    
    
    
    
    private function getItem($key)
    {
        $data = false;
        
        if (isset($this->data[$key])) {
            $data = $this->data[$key];
        } else {
            $data = apc_fetch($key);
            $this->data[$key] = $data;
        }
        
        return $data;
    }
}
