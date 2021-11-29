<?php

namespace App\Http\Service;

abstract class WeComInterface
{
    /**
     * @param string $WeComID
     * @param string $Content
     * @param array  $Message
     */
    abstract public static function handle(string $WeComID, string $Content, array $Message, array $matches = []): ?string;
}
