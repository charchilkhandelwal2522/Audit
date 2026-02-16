<?php

namespace Modules\Audit\Jobs;

use App\Helper\Files;
use App\Models\StorageSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Modules\Audit\Entities\Audit;
use Modules\Audit\Entities\AuditFile;

class ExportAuditPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max images to embed in PDF (null = no limit, embed all).
     * Images are resized to MAX_IMAGE_DIMENSION for performance.
     */
    public const MAX_EMBEDDED_IMAGES = null;

    /**
     * Max dimension for embedded images (resize to speed up dompdf).
     */
    public const MAX_IMAGE_DIMENSION = 400;

    public const CACHE_TTL = 3600;

    public int $timeout = 1800;

    public function __construct(
        public int $auditId,
        public string $exportToken,
        public ?int $userId = null
    ) {}

    public function handle(): void
    {
        set_time_limit(1800);
        @ini_set('memory_limit', '512M');
        $this->updateProgress(5, __('audit::app.loadingData'));

        $audit = Audit::with([
            'template',
            'department',
            'auditor',
            'auditee',
            'responses.checkpoint',
            'responses.files',
        ])->findOrFail($this->auditId);

        $tempDir = storage_path('app/temp/audit-pdf/' . $this->exportToken);
        File::ensureDirectoryExists($tempDir);

        try {
            $totalImages = $audit->responses->flatMap->files->filter(fn ($f) => $f->isImage())->count();
            $maxToProcess = self::MAX_EMBEDDED_IMAGES === null ? $totalImages : min($totalImages, self::MAX_EMBEDDED_IMAGES);

            $this->updateProgress(15, __('audit::app.preparingImages', ['current' => 0, 'total' => $maxToProcess]));

            $embedMap = $this->buildEmbedMap($audit);

            $this->updateProgress(50, __('audit::app.generatingReport'));

            $pdf = app('dompdf.wrapper');
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->loadView('audit::audits.pdf.report', [
                'audit' => $audit,
                'embedMap' => $embedMap,
            ]);
            $finalPath = $tempDir . '/audit-report-' . $audit->id . '.pdf';
            File::put($finalPath, $pdf->output());

            $this->updateProgress(90, __('audit::app.savingPdf'));

            $filename = 'audit-report-' . $audit->id . '-' . time() . '.pdf';
            $storagePath = 'audit-pdf-exports/' . $this->exportToken . '/' . $filename;

            Storage::disk('storage')->put(
                $storagePath,
                File::get($finalPath)
            );

            File::deleteDirectory($tempDir);

            $this->updateProgress(100, __('audit::app.pdfReady'), [
                'status' => 'ready',
                'download_path' => $storagePath,
                'filename' => 'audit-report-' . $audit->id . '.pdf',
            ]);
        } catch (\Throwable $e) {
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            $this->updateProgress(0, $e->getMessage(), [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Build embed map with resized images for faster PDF generation.
     */
    protected function buildEmbedMap(Audit $audit): array
    {
        $embedMap = [];
        $count = 0;
        $imageCount = $audit->responses->flatMap->files->filter(fn ($f) => $f->isImage())->count();
        $total = self::MAX_EMBEDDED_IMAGES === null ? $imageCount : min($imageCount, self::MAX_EMBEDDED_IMAGES);

        foreach ($audit->responses as $response) {
            foreach ($response->files as $file) {
                if ($file->isImage() && (self::MAX_EMBEDDED_IMAGES === null || $count < self::MAX_EMBEDDED_IMAGES)) {
                    $path = $this->getImagePathForPdf($file);
                    if ($path) {
                        $embedMap[$file->id] = $path;
                        $count++;
                        if ($count % 5 === 0) {
                            $this->updateProgress(15 + (int) (35 * $count / max(1, $total)), __('audit::app.preparingImages', [
                                'current' => $count,
                                'total' => $total,
                            ]));
                        }
                    }
                }
            }
        }

        return $embedMap;
    }

    protected function getImagePathForPdf(AuditFile $file): ?string
    {
        $path = 'audit-files/' . $file->hashname;
        $tempDir = storage_path('app/temp/audit-pdf/' . $this->exportToken . '/images');
        File::ensureDirectoryExists($tempDir);
        $ext = strtolower(pathinfo($file->hashname, PATHINFO_EXTENSION));
        $resizePath = $tempDir . '/resized_' . $file->hashname;

        $content = null;
        if (in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE)) {
            try {
                $content = Storage::disk(config('filesystems.default'))->get($path);
            } catch (\Throwable) {
                return null;
            }
        } else {
            $localPath = public_path(Files::UPLOAD_FOLDER . '/' . $path);
            if (!File::exists($localPath)) {
                return null;
            }
            $content = File::get($localPath);
        }

        $tempPath = $tempDir . '/' . $file->hashname;
        File::put($tempPath, $content);

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            try {
                Image::make($tempPath)
                    ->resize(self::MAX_IMAGE_DIMENSION, self::MAX_IMAGE_DIMENSION, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->save($resizePath, 85);
                File::delete($tempPath);
                return $resizePath;
            } catch (\Throwable) {
                return $tempPath;
            }
        }

        return $tempPath;
    }

    protected function updateProgress(int $percent, string $message, array $extra = []): void
    {
        $key = 'audit_pdf_export_' . $this->exportToken;
        $data = array_merge(
            Cache::get($key, []),
            ['progress' => $percent, 'message' => $message],
            $extra
        );
        Cache::put($key, $data, self::CACHE_TTL);
    }
}
