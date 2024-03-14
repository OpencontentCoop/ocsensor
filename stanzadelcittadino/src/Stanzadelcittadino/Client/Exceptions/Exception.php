<?php

namespace Opencontent\Stanzadelcittadino\Client\Exceptions;

use Exception as BaseException;
use Throwable;

class Exception extends BaseException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($message)){
            $message = get_called_class() . ' exception was raised';
        }
        parent::__construct($message, $code, $previous);
    }

}