<?php
namespace EC\Exceptions;
use Exception;

/**
 * Extends Exception class to hold info on a Model that didn't pass Validations
 */
class InvalidModelException extends Exception{
    protected $message = 'We\'ve found some errors. Please, verify.';
}