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
     * GET /api/v1/export/career-run/{plan}
     */
    public function exportPlan(Plan $plan, Request $request): Response|JsonResponse
    {
        $format = $request->input('format', 'json');
        $download = $request->boolean('download', true);

        $validFormats = ['json', 'csv', 'markdown', 'md'];
        if (!in_array($format, $validFormats, true)) {
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
            'csv' => 'text/csv',
            'markdown', 'md' => 'text/markdown',
            default => 'application/json',
        };

        if ($download) {
            $safeFileName = preg_replace('/[^a-z0-9_]/i', '_', $plan->plan_title ?? 'plan');
            $fileName = "{$safeFileName}_{$plan->id}.{$extension}";

            return new Response($content, 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);
        }

        return response()->json([
            'data' => [
                'content' => $content,
                'format' => $format,
                'size' => strlen($content),
            ],
            'meta' => [
                'plan_id' => $plan->id,
                'plan_title' => $plan->plan_title,
                'schema_version' => $this->exportService->getSchemaVersion(),
            ],
        ]);
    }

    /**
     * Get export preview.
     * GET /api/v1/export/career-run/{plan}/preview
     */
    public function preview(Plan $plan, Request $request): JsonResponse
    {
        $format = $request->input('format', 'json');

        $validFormats = ['json', 'csv', 'markdown', 'md'];
        if (!in_array($format, $validFormats, true)) {
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
     * POST /api/v1/export/bulk
     */
    public function exportBulk(Request $request): Response|JsonResponse
    {
        $validated = $request->validate([
            'plan_ids' => 'required|array|min:1|max:50',
            'plan_ids.*' => 'required|integer|exists:plans,id',
            'format' => 'nullable|string|in:json',
        ]);

        $plans = Plan::whereIn('id', $validated['plan_ids'])->get();

        if ($plans->isEmpty()) {
            return response()->json([
                'error' => 'No plans found',
                'message' => 'None of the specified plan IDs were found.',
            ], 404);
        }

        $content = $this->exportService->plansToJson($plans);

        $download = $request->boolean('download', true);

        if ($download) {
            $fileName = 'plans_export_' . now()->format('Y-m-d_His') . '.json';

            return new Response($content, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);
        }

        return response()->json([
            'data' => [
                'content' => $content,
                'format' => 'json',
                'size' => strlen($content),
                'count' => $plans->count(),
            ],
            'meta' => [
                'schema_version' => $this->exportService->getSchemaVersion(),
            ],
        ]);
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
