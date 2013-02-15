<?php

namespace RPI\Framework\Cache\Data;

/**
 * APC cache support wrapper
 * Supports file dependencies and time to live on cached data
 *
 * @author Matt Dunn
 */
class Apc implements \RPI\Framework\Cache\IData, \RPI\Framework\Cache\Data\ISupportsPatternDelete
{
    private $isAvailable = null;

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        if ($this->isAvailable === null) {
            $this->isAvailable = extension_loaded("APC");
        }

        return $this->isAvailable;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key, $autoDelete = true, &$existingCacheData = null)
    {
        if ($this->isAvailable() === true) {
            $data = apc_fetch($key);
            if ($data === false) {
                return false;
            }

            $existingCacheData = $data["value"];

            if (isset($data["fileDep"]) && isset($data["fileDep_mod"])) {
                if (is_array($data["fileDep"])) {
                    $fileCount = count($data["fileDep"]);
                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($data["fileDep_mod"][$i] != filemtime($data["fileDep"][$i])) {
                            if ($autoDelete && function_exists("apc_delete")) {
                                $this->delete($key);
                            }

                            return false;
                        }
                    }
                } else {
                    if ($data["fileDep_mod"] != filemtime($data["fileDep"])) {
                        if ($autoDelete && function_exists("apc_delete")) {
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

                return apc_store(
                    $key,
                    array("value" => $value, "fileDep" => $fileDep, "fileDep_mod" => $fileDepMod),
                    $ttl
                );
            } else {
                return apc_store(
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
            return apc_clear_cache("user");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        if ($this->isAvailable() === true) {
            return apc_delete($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deletePattern($pattern)
    {
        foreach (new \APCIterator("user", $pattern) as $counter) {
            $this->delete($counter["key"]);
        }
    }
}
