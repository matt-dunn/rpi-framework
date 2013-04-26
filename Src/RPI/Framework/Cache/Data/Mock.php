<?php

namespace RPI\Framework\Cache\Data;

/**
 * Mock cache support wrapper
 * Supports file dependencies and time to live on cached data
 *
 * @author Matt Dunn
 */
class Mock implements \RPI\Framework\Cache\IData
{
    private $data = array();
    
    /**
     * {@inheritdoc}
     */
    public function fetch($key, $autoDelete = true, &$existingCacheData = null)
    {
        if (!isset($this->data[$key])) {
            return false;
        }

        $data = $this->data[$key];

        $existingCacheData = $data["value"];

        if (isset($data["expire"]) && microtime(true) > $data["expire"]) {
            $this->delete($key);

            return false;
        }

        if (isset($data["fileDep"]) && isset($data["fileDep_mod"])) {
            if (is_array($data["fileDep"])) {
                $fileCount = count($data["fileDep"]);
                for ($i = 0; $i < $fileCount; $i++) {
                    if (filemtime($data["fileDep"][$i]) > $data["fileDep_mod"][$i]) {
                        if ($autoDelete) {
                            $this->delete($key);
                        }

                        return false;
                    }
                }
            } else {
                if (filemtime($data["fileDep"]) > $data["fileDep_mod"]) {
                    if ($autoDelete) {
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
        $expire = null;
        if (isset($ttl) && $ttl > 0) {
            $expire = microtime(true) + $ttl;
        }

        if (is_array($fileDep)) {
            $fileDepMod = array();
            foreach ($fileDep as $file) {
                $fileDepMod[] = filemtime($file);
            }

            return $this->data[$key] = array(
                "expire" => $expire,
                "value" => $value,
                "modified" => microtime(true),
                "fileDep" => $fileDep,
                "fileDep_mod" => $fileDepMod
            );
        } else {
            return $this->data[$key] = array(
                "expire" => $expire,
                "value" => $value,
                "modified" => microtime(true),
                "fileDep" => $fileDep,
                "fileDep_mod" => filemtime($fileDep)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->data = array();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deletePattern($pattern)
    {
        $deleteCount = 0;
        foreach ($this->data as $key => $value) {
            if (preg_match($pattern, $key) !== 0) {
                unset($this->data[$key]);
                $deleteCount++;
            }
        }

        return $deleteCount;
    }
    
    /**
     * Helper method for unit testing
     * 
     * @return mixed
     */
    public function getData()
    {
        $data = array();
        
        foreach ($this->data as $key => $value) {
            $data[$key] = $value["value"];
        }
        
        return $data;
    }

    public function getItemModifiedTime($key)
    {
        if (!isset($this->data[$key])) {
            return false;
        }

        $data = $this->data[$key];

        if (isset($data["modified"])) {
            return (double)$data["modified"];
        }
        
        return false;
    }
}
