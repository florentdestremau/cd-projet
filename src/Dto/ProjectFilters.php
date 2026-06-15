<?php

namespace App\Dto;

use App\Enum\ProjectStage;
use App\Enum\ProjectStatus;

final class ProjectFilters
{
    public function __construct(
        public ?ProjectStatus $status = null,
        public ?ProjectStage $stage = null,
        public string $q = '',
    ) {
    }
}
