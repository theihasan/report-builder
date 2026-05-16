<?php

namespace Ihasan\ReportBuilder\Http\Controllers\Api;

use Ihasan\ReportBuilder\Contracts\DataSourceContract;
use Ihasan\ReportBuilder\DTOs\FieldDefinition;
use Ihasan\ReportBuilder\Exceptions\DataSourceNotFoundException;
use Ihasan\ReportBuilder\ReportBuilder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DataSourceController
{
    public function __construct(protected ReportBuilder $reportBuilder) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => array_values(array_map(
                fn (DataSourceContract $dataSource): array => $this->transformSourceSummary($dataSource),
                $this->reportBuilder->authorizedDataSources($request)
            )),
        ]);
    }

    public function show(Request $request, string $source): JsonResponse
    {
        try {
            $dataSource = $this->reportBuilder->dataSource($source);
        } catch (DataSourceNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }

        if (! $dataSource->authorize($request)) {
            throw new AuthorizationException('You are not allowed to access this report data source.');
        }

        return response()->json([
            'data' => $this->transformSourceDetail($dataSource),
        ]);
    }

    /**
     * @return array{key: string, label: string}
     */
    protected function transformSourceSummary(DataSourceContract $dataSource): array
    {
        return [
            'key' => $dataSource->key(),
            'label' => $dataSource->label(),
        ];
    }

    /**
     * @return array{key: string, label: string, fields: array<int, array<string, mixed>>}
     */
    protected function transformSourceDetail(DataSourceContract $dataSource): array
    {
        return [
            'key' => $dataSource->key(),
            'label' => $dataSource->label(),
            'fields' => array_values(array_map(
                static fn (FieldDefinition $field): array => $field->toArray(),
                $dataSource->fields()
            )),
        ];
    }
}
