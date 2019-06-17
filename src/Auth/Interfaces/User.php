<?php
namespace EC\Auth\Interfaces;

/**
 * User interface
 * As this class is used for authentication, at leas this method should be implemented.
 */
interface User{

    public function authenticate(string $password);
}