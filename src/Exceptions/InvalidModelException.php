<?php
namespace EC\Exceptions;
use Exception;

class InvalidModelException extends Exception{
    protected $message = 'Houveram erros, favor verificar.';
}