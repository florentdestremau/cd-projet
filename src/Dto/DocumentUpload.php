<?php

namespace App\Dto;

use App\Enum\DocumentCategory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class DocumentUpload
{
    public function __construct(
        public ?UploadedFile $file = null,
        public DocumentCategory $category = DocumentCategory::OTHER,
    ) {
    }
}
