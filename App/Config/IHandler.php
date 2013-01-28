<?php

namespace RPI\Framework\App\Config;

interface IHandler
{
    public function process(array $config);
}
