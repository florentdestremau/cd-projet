<?php

namespace App\Dto;

final class CompanySettings
{
    public function __construct(
        public string $companyName = '',
        public ?string $companyTagline = null,
        public ?string $companyAddress = null,
        public ?string $companyEmail = null,
        public ?string $companyPhone = null,
        public ?string $companyLegal = null,
        public string $defaultVatRate = '20.00',
    ) {
    }
}
