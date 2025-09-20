<?php

/**
 * @author Rufusy Idachi
 * @email idachirufus@gmail.com
 * @create date 23-02-2021 23:26:04 
 * @modify date 23-02-2021 23:26:04 
 * @desc [description]
 */

namespace app\exceptions;

use Yii;
use Exception;

class ForbiddenException extends Exception{
    
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}