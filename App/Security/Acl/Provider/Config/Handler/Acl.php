<?php

namespace RPI\Framework\App\Security\Acl\Provider\Config\Handler;

class Acl implements \RPI\Framework\App\Config\IHandler
{
    /**
     *
     * @var \RPI\Framework\Cache\IData
     */
    private $store = null;
    
    /**
     *
     * @var string
     */
    private $cacheKeyPrefix = null;
    
    public function __construct(\RPI\Framework\Cache\IData $store, $cacheKeyPrefix)
    {
        $this->store = $store;
        $this->cacheKeyPrefix = $cacheKeyPrefix;
    }
    
    public function process(array $config)
    {
        if (isset($config["access"]["roles"]["role"]["@"])) {
            $config["access"]["roles"]["role"] = array($config["access"]["roles"]["role"]);
        }
        
        foreach ($config["access"]["roles"]["role"] as $role) {
            $roleName = $role["@"]["name"];
            unset($role["@"]);
            $config["access"]["roles"][$roleName] = $role;
            
            if (isset($config["access"]["roles"][$roleName]["properties"])) {
                $config["access"]["roles"][$roleName]["properties"] =
                    $this->readSection($config["access"]["roles"][$roleName], "properties", "property");
            }
            
            if (isset($config["access"]["roles"][$roleName]["operations"])) {
                $config["access"]["roles"][$roleName]["operations"] =
                    $this->readSection($config["access"]["roles"][$roleName], "operations", "operation");
            } elseif (isset($config["access"]["roles"][$roleName]["properties"])) {
                $config["access"]["roles"][$roleName]["operations"] = array("*" => null);
                foreach ($config["access"]["roles"][$roleName]["properties"] as $property) {
                    $config["access"]["roles"][$roleName]["operations"]["*"] |= $property;
                }
            }
        }
        
        unset($config["access"]["roles"]["role"]);
        
        if (isset($config["@"]["name"])) {
            if (preg_match(
                "/^((((([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\\\{1})*)".
                "[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\s*,{1}\s*)*)".
                "(((([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\\\{1})*)".
                "[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\s*)$/",
                $config["@"]["name"]
            ) !== 1) {
                throw new \RPI\Framework\Exceptions\InvalidArgument(
                    $config["@"]["name"],
                    null,
                    "Name must be a comma separated list of valid namespaced objects"
                );
            }

            $names = explode(",", $config["@"]["name"]);
            
            unset($config["@"]["name"]);
            unset($config["@"]["handler"]);
            if (count($config["@"]) == 0) {
                unset($config["@"]);
            }
            
            foreach ($names as $name) {
                $name = trim($name);
                if ($name != "") {
                    if ($this->store->fetch($this->cacheKeyPrefix.$name, false) === false) {
                        $this->store->store($this->cacheKeyPrefix.$name, $config);
                    } else {
                        throw new \RPI\Framework\Exceptions\InvalidArgument(
                            $name,
                            null,
                            "Name has already been defined"
                        );
                    }
                }
            }
            
            return null;
        } else {
            throw new \RPI\Framework\Exceptions\Exception("Invalid data format");
        }
    }
    
    private function readSection(array $role, $section, $sectionItem)
    {
        if (isset($role[$section], $role[$section][$sectionItem])) {
            if (isset($role[$section][$sectionItem]["@"])) {
                $role[$section][$sectionItem] = array($role[$section][$sectionItem]);
            }

            foreach ($role[$section][$sectionItem] as $operation) {
                $operationName = $operation["@"]["name"];
                
                $permissions = str_replace(
                    "Acl::",
                    "RPI\Framework\App\Security\Acl\Model\IAcl::",
                    $operation["@"]["permissions"]
                );
               
                $role[$section][$operationName] = eval("return ".$permissions.";");
            }

            unset($role[$section][$sectionItem]);
        }
        
        return $role[$section];
    }
}
