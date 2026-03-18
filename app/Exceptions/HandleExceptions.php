<?php

declare(strict_types=1);

namespace App\Exceptions;

class HandleExceptions extends \Illuminate\Foundation\Bootstrap\HandleExceptions
{
    /**
     * {@inheritdoc}
     */
    protected function shouldIgnoreDeprecationErrors()
    {
        return true;
    }
}
