<?php

namespace App\Dto;

final class ClientFilters
{
    public function __construct(public string $q = '')
    {
    }
}
