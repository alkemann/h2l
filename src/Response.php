<?php

namespace alkemann\h2l;

/**
 * Abstract class Response
 *
 * @package alkemann\h2l
 */
abstract class Response extends Message
{

    protected static $contentTypes = [
        'text/html' => 'html',
        'application/json' => 'json',
        'application/xml' => 'xml',
        'text/xml' => 'xml',
    ];

    public function fileEndingFromType(string $type): string
    {
        foreach (static::$contentTypes as $type_key => $ending) {
            if ($type === $type_key) {
                return $ending;
            }
        }
        return 'html';
    }

    abstract public function render(): string;
}
