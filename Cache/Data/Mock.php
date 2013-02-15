<?php

namespace RPI\Framework\Cache\Data;

/**
 * Mock cache support wrapper
 * Supports file dependencies and time to live on cached data
 *
 * @author Matt Dunn
 */
class Mock implements \RPI\Framework\Cache\IData, \RPI\Framework\Cache\Data\ISupportsPatternDelete
{
    private $data = array();
    
    public function isAvailable()
    {
        return true;
    }

    public function fetch($key, $autoDelete = true, &$existingCacheData = null)
    {
        if ($this->isAvailable() === true) {
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

    public function store($key, $value, $fileDep = null, $ttl = 0)
    {
        if ($this->isAvailable() === true) {
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
                    "expire" => $expire, "value" => $value, "fileDep" => $fileDep, "fileDep_mod" => $fileDepMod
                );
            } else {
                return $this->data[$key] = array(
                    "expire" => $expire, "value" => $value, "fileDep" => $fileDep, "fileDep_mod" => filemtime($fileDep)
                );
            }
        } else {
            return false;
        }
    }

    public function clear()
    {
        $this->data = array();
    }

    public function delete($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
            return true;
        }
        
        return false;
    }

    public function deletePattern($pattern)
    {
        foreach ($this->data as $key => $value) {
            if (preg_match($pattern, $key) !== 0) {
                unset($this->data[$key]);
            }
        }
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
}
