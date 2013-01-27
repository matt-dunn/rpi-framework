<?php

// ================================================================================================================
// Shortcut funtions:

// Localisation
function t($id, $args = null)
{
    $localisationService = \RPI\Framework\Helpers\Reflection::getDependency(
        $GLOBALS["RPI_APP"],
        null,
        null,
        "RPI\Framework\Services\Localisation\ILocalisation"
    );
    
    if (!isset($localisationService)) {
        throw new \Exception("RPI\Framework\Services\Localisation\ILocalisation dependency not configured correctly");
    }
    
    return $localisationService->t($id, $args);
}
