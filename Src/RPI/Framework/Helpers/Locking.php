<?php

namespace RPI\Framework\Helpers;

/**
 * Helper class to assist with semaphore locking
 */
class Locking
{
    private function __construct()
    {
    }

    // Lock a resource
    public static function lock($id)
    {
        if (function_exists("sem_get") && function_exists("sem_acquire")) {
            // If the id passed is not an integer, convert it into a int safe value based on a string value
            if (is_string($id)) {
                $intId = 0;
                $idLength = strlen($id);
                for ($i = 0; $i < $idLength; $i++) {
                    $intId += (ord($id[$i]) * pow(2, $i));
                }
                $id = $intId;
            }
            $seg = sem_get($id);
            sem_acquire($seg);

            return $seg;
        } else {
            return false;
        }
    }

    // Release a resource
    public static function release($resourceId)
    {
        if (function_exists("sem_release") && $resourceId !== false) {
            try {
                sem_release($resourceId);
            } catch (\Exception $ex) {
                // Do nothing. An exception will be thrown if a semaphore has already been released...
            }
        } else {
            return false;
        }
    }
}
