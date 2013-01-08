<?php

namespace RPI\Framework\Test;

class AllTests
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite('RPI_Framework');

        $files = array();
        $basePath = realpath(dirname(__FILE__)."/../../");
        \RPI\Framework\Helpers\FileUtils::find($basePath, "/.*Test\.php$/", $files, true, false);
        $files = array_keys($files);

        foreach ($files as $file) {
            $testClassName = "RPI".str_replace(".php", "", str_replace("/", "\\", substr($file, strlen($basePath))));
            echo "Found test '$testClassName'\n";
            $suite->addTestSuite($testClassName);

        }

        return $suite;
    }
}
