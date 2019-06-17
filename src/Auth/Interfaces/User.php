<?php
namespace EC\Auth\Interfaces;

interface User{

    public function authenticate(string $password);
}