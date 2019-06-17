<?php
namespace EC\Exceptions;
use Exception;

/**
 * Extends Exception class to hold info on a Model that was not found.
 */
class ModelNotFoundException extends Exception{
    protected $message = 'Model was not found.';
}