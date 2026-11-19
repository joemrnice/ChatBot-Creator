<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\ExportService;

class ExportController
{
    private ExportService $exportService;

    public function __construct()
    {
        $this->exportService = new ExportService();
    }

    public function json(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');

        try {
            $data = $this->exportService->exportJsonConfig($botId, $userId);
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $filename = 'chatbot-' . ($data['bot']['name'] ? preg_replace('/[^a-z0-9]+/i', '-', strtolower($data['bot']['name'])) : 'config') . '.json';

            return Response::make($json, 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (\Throwable $e) {
            Session::flash('error', 'Export Failed: ' . $e->getMessage());
            return Response::back();
        }
    }

    public function csv(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');

        try {
            $csv = $this->exportService->exportKnowledgeCsv($botId, $userId);
            return Response::make($csv, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="knowledge-base.csv"',
            ]);
        } catch (\Throwable $e) {
            Session::flash('error', 'CSV Export Failed: ' . $e->getMessage());
            return Response::back();
        }
    }

    public function conversations(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');

        try {
            $csv = $this->exportService->exportConversationsCsv($botId, $userId);
            return Response::make($csv, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="conversations-report.csv"',
            ]);
        } catch (\Throwable $e) {
            Session::flash('error', 'Conversations Export Failed: ' . $e->getMessage());
            return Response::back();
        }
    }

    public function zip(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');

        try {
            $zipPath = $this->exportService->generateZipPackage($botId, $userId);
            return Response::download($zipPath, basename($zipPath), 'application/zip');
        } catch (\Throwable $e) {
            Session::flash('error', 'ZIP Generation Failed: ' . $e->getMessage());
            return Response::back();
        }
    }
}
