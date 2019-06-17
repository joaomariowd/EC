<?php
namespace EC\Exceptions;
use Exception;

class ModelNotFoundException extends Exception{
    protected $message = 'Não foi encontrado.';
}