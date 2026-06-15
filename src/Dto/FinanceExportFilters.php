<?php

namespace App\Dto;

final class FinanceExportFilters
{
    public function __construct(
        public string $type = 'invoices',
        public string $from = '',
        public string $to = '',
    ) {
    }

    public function fromDate(): \DateTimeImmutable
    {
        return '' !== $this->from ? new \DateTimeImmutable($this->from) : new \DateTimeImmutable(date('Y-01-01'));
    }

    public function toDate(): \DateTimeImmutable
    {
        return '' !== $this->to ? new \DateTimeImmutable($this->to) : new \DateTimeImmutable(date('Y-m-d'));
    }
}
