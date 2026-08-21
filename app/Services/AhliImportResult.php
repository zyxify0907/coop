<?php

namespace App\Services;

use App\Models\AhliImport;

class AhliImportResult
{
    public function __construct(
        public readonly int $importedCount,
        public readonly int $failedCount,
        public readonly AhliImport $log,
    ) {}
}
