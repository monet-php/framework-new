<?php

namespace Monet\Framework\Auth\Contracts;

interface ShouldVerifyEmail
{
    public function shouldVerifyEmail(): bool;
}
