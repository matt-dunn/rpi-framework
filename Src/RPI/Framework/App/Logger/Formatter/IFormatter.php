<?php

namespace RPI\Framework\App\Logger\Formatter;

interface IFormatter
{
    public function format(array $record);
}
