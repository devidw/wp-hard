<?php

declare(strict_types=1);

namespace Devidw\Hard\Harden;

/**
 * Disable Theme and Plugin File Editors
 */
class FileEdit
{
    public function __construct()
    {
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
    }
}
