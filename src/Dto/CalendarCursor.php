<?php

namespace App\Dto;

final class CalendarCursor
{
    public function __construct(public string $m = '')
    {
    }

    public function date(): \DateTimeImmutable
    {
        if ('' === $this->m) {
            return new \DateTimeImmutable('first day of this month');
        }
        try {
            return new \DateTimeImmutable($this->m.'-01');
        } catch (\Exception) {
            return new \DateTimeImmutable('first day of this month');
        }
    }
}
