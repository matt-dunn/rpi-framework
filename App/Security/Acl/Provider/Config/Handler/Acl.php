<?php

namespace RPI\Framework\App\Security\Acl\Provider\Config\Handler;

class Acl implements \RPI\Framework\App\Config\IHandler
{
    public function process(array $config)
    {
        if (isset($config["access"]["roles"]["role"]["@"])) {
            $config["access"]["roles"]["role"] = array($config["access"]["roles"]["role"]);
        }
        
        foreach ($config["access"]["roles"]["role"] as $role) {
            $roleName = $role["@"]["name"];
            unset($role["@"]);
            $config["access"]["roles"][$roleName] = $role;
            
            if (isset($config["access"]["roles"][$roleName]["operations"])) {
                $config["access"]["roles"][$roleName]["operations"] = $this->readSection($config["access"]["roles"][$roleName], "operations", "operation");
            }
            
            if (isset($config["access"]["roles"][$roleName]["properties"])) {
                $config["access"]["roles"][$roleName]["properties"] = $this->readSection($config["access"]["roles"][$roleName], "properties", "property");
            }
        }
        
        unset($config["access"]["roles"]["role"]);
        

        if (isset($config["@"]["name"])) {
            $name = $config["@"]["name"];
            unset($config["@"]["name"]);
            unset($config["@"]["handler"]);
            if (count($config["@"]) == 0) {
                unset($config["@"]);
            }
            
            return array(
                "name" => $name,
                "value" => $config
            );
        } else {
            return $config;
        }
    }
    
    function readSection(array $role, $section, $sectionItem)
    {
        if (isset($role[$section])) {
            if (isset($role[$section][$sectionItem]["@"])) {
                $role[$section][$sectionItem] = array($role[$section][$sectionItem]);
            }

            foreach ($role[$section][$sectionItem] as $operation) {
                $operationName = $operation["@"]["name"];
                
                $permissions = str_replace("Acl::", "RPI\Framework\App\Security\Acl::", $operation["@"]["permissions"]);
               
                $role[$section][$operationName] = eval("return ".$permissions.";");
            }

            unset($role[$section][$sectionItem]);
        }
        
        return $role[$section];
    }
}
