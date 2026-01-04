<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Export API Controller
 *
 * Handles data export via API.
 * Implements API Design requirements from design.md.
 */
class ExportController extends Controller
{
    public function __construct(
        private readonly ExportService $exportService
    ) {}

    /**
     * Export a single plan.
     * GET /api/v1/plans/{plan}/export/{format}
     */
    public function exportPlan(Plan $plan, Request $request, ?string $format = null): Response|JsonResponse
    {
        // Format can come from route parameter or query string
        $format = $format ?? $request->input('format', 'json');
        $download = $request->boolean('download', true);

        $validFormats = ['json', 'csv', 'markdown', 'md'];
        if (! \in_array($format, $validFormats, true)) {
            return response()->json([
                'error' => 'Invalid format',
                'message' => 'Format must be one of: json, csv, markdown, md',
            ], 400);
        }

        $content = match ($format) {
            'csv' => $this->exportService->toCsv($plan),
            'markdown', 'md' => $this->exportService->toMarkdown($plan),
            default => $this->exportService->toJson($plan),
        };

        $extension = match ($format) {
            'csv' => 'csv',
            'markdown', 'md' => 'md',
            default => 'json',
        };

        $contentType = match ($format) {
            'csv' => 'text/csv; charset=UTF-8',
            'markdown', 'md' => 'text/markdown; charset=UTF-8',
            default => 'application/json',
        };

        // For JSON format, return structured response
        if ($format === 'json') {
            $data = json_decode($content, true);

            return response()->json($data, 200, [
                'Content-Type' => $contentType,
            ]);
        }

        // For CSV and Markdown, return raw content
        return new Response($content, 200, [
            'Content-Type' => $contentType,
        ]);
    }

    /**
     * Get export preview.
     * GET /api/v1/plans/{plan}/export/preview
     */
    public function preview(Plan $plan, Request $request): JsonResponse
    {
        $format = $request->input('format', 'json');

        $validFormats = ['json', 'csv', 'markdown', 'md'];
        if (! \in_array($format, $validFormats, true)) {
            return response()->json([
                'error' => 'Invalid format',
                'message' => 'Format must be one of: json, csv, markdown, md',
            ], 400);
        }

        $preview = $this->exportService->getPreview($plan, $format);

        return response()->json([
            'data' => $preview,
            'meta' => [
                'plan_id' => $plan->id,
                'schema_version' => $this->exportService->getSchemaVersion(),
            ],
        ]);
    }

    /**
     * Export multiple plans.
     * GET /api/v1/export/plans?ids=1,2,3
     */
    public function exportBulk(Request $request): Response|JsonResponse
    {
        // Support both comma-separated string and array format
        $idsInput = $request->input('ids');

        if (empty($idsInput)) {
            return response()->json([
                'message' => 'The ids field is required.',
                'errors' => ['ids' => ['The ids field is required.']],
            ], 422);
        }

        // Parse comma-separated IDs
        $planIds = \is_array($idsInput)
            ? $idsInput
            : array_filter(array_map('intval', explode(',', $idsInput)));

        if (empty($planIds)) {
            return response()->json([
                'message' => 'The ids field is required.',
                'errors' => ['ids' => ['The ids field is required.']],
            ], 422);
        }

        $plans = Plan::whereIn('id', $planIds)->get();

        if ($plans->isEmpty()) {
            return response()->json([
                'error' => 'No plans found',
                'message' => 'None of the specified plan IDs were found.',
            ], 404);
        }

        $exportData = $this->exportService->plansToArray($plans);

        return response()->json($exportData);
    }

    /**
     * Get available export formats.
     * GET /api/v1/export/formats
     */
    public function formats(): JsonResponse
    {
        return response()->json([
            'data' => [
                [
                    'id' => 'json',
                    'name' => 'JSON',
                    'extension' => '.json',
                    'content_type' => 'application/json',
                    'description' => 'Full structured data export',
                ],
                [
                    'id' => 'csv',
                    'name' => 'CSV',
                    'extension' => '.csv',
                    'content_type' => 'text/csv',
                    'description' => 'Spreadsheet-compatible format',
                ],
                [
                    'id' => 'markdown',
                    'name' => 'Markdown',
                    'extension' => '.md',
                    'content_type' => 'text/markdown',
                    'description' => 'Human-readable text format',
                ],
            ],
            'meta' => [
                'schema_version' => $this->exportService->getSchemaVersion(),
            ],
        ]);
    }
}
