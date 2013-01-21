<?php

namespace RPI\Framework\Test;

class AllTests
{
    public static function suite()
    {
        $rootPath = realpath(__DIR__."/../../../");
        $basePath = realpath(__DIR__."/../../../");
        
        \RPI\Framework\Helpers\FileUtils::find($basePath, "/.*Test\.php$/", $files, true, false);
        $files = array_keys($files);

        $suites = array();
        
        foreach ($files as $file) {
            $suiteName = str_replace(DIRECTORY_SEPARATOR, "\\", substr($basePath, strlen($rootPath) + 1));
            if (isset($suites[$suiteName])) {
                $suite = $suites[$suiteName];
            } else {
                $suite = new \PHPUnit_Framework_TestSuite($suiteName);
                $suites[$suiteName] = $suite;
            }
            
            $testClassName = $suiteName.str_replace(".php", "", str_replace("/", "\\", substr($file, strlen($basePath))));
            echo "Found test '$testClassName' (suite: '$suiteName')\n";
            $suite->addTestSuite($testClassName);
        }

        return $suite;
    }
}
