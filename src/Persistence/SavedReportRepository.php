<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\Persistence;

use Ihasan\ReportBuilder\DTOs\ReportDefinition;
use Ihasan\ReportBuilder\Models\SavedReport;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use Throwable;

class SavedReportRepository
{
    public function saveDefinition(
        string $name,
        ReportDefinition $definition,
        ?int $createdBy = null,
        string $visibility = 'private',
        ?string $publicId = null,
    ): SavedReport {
        return SavedReport::query()->create([
            'name' => $name,
            'public_id' => $publicId ?? $this->generatePublicId(),
            'source_key' => $definition->sourceKey(),
            'definition' => $definition->toArray(),
            'created_by' => $createdBy,
            'visibility' => $visibility,
        ]);
    }

    public function loadDefinition(SavedReport $savedReport): ReportDefinition
    {
        $rawDefinition = $savedReport->getRawOriginal('definition');

        try {
            if (is_string($rawDefinition)) {
                return ReportDefinition::fromJson($rawDefinition);
            }

            if (is_array($savedReport->definition)) {
                return ReportDefinition::fromArray($savedReport->definition);
            }
        } catch (JsonException|Throwable $exception) {
            throw new InvalidArgumentException('Saved report definition is malformed.', 0, $exception);
        }

        throw new InvalidArgumentException('Saved report definition is malformed.');
    }

    public function updateDefinition(SavedReport $savedReport, ReportDefinition $definition): SavedReport
    {
        $savedReport->fill([
            'source_key' => $definition->sourceKey(),
            'definition' => $definition->toArray(),
        ]);

        $savedReport->save();

        return $savedReport->refresh();
    }

    private function generatePublicId(): string
    {
        return (string) Str::uuid();
    }
}
