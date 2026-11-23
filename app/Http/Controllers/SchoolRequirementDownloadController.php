<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Services\SchoolDocumentWatermark;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SchoolRequirementDownloadController extends Controller
{
    public function __invoke(School $school, int $document, SchoolDocumentWatermark $watermark)
    {
        $school = School::query()->publiclyVisible()->whereKey($school->id)->firstOrFail();
        $record = $school->documents()->whereKey($document)->firstOrFail();

        $sourcePath = tempnam(sys_get_temp_dir(), 'findursch-source-');
        if ($sourcePath === false) {
            abort(503, 'The document could not be prepared right now.');
        }

        $watermarkedPath = null;

        try {
            $encrypted = Storage::disk('school_documents')->get($record->stored_path);
            $plainText = Crypt::decryptString($encrypted);

            if (strlen($plainText) > 20 * 1024 * 1024) {
                throw new \RuntimeException('The source document exceeds the permitted size.');
            }

            file_put_contents($sourcePath, $plainText, LOCK_EX);
            $extension = strtolower(pathinfo($record->original_filename, PATHINFO_EXTENSION));
            $watermarkedPath = $watermark->createWatermarkedCopy($sourcePath, $extension);
        } catch (Throwable $exception) {
            report($exception);
            @unlink($sourcePath);
            if ($watermarkedPath && is_file($watermarkedPath)) {
                @unlink($watermarkedPath);
            }

            return response('This school document could not be prepared for download. Please contact the school and try again.', 422)
                ->header('Cache-Control', 'private, no-store, max-age=0')
                ->header('X-Content-Type-Options', 'nosniff');
        }

        @unlink($sourcePath);

        $baseName = Str::slug(pathinfo($record->original_filename, PATHINFO_FILENAME)) ?: 'school-requirements';
        $extension = strtolower(pathinfo($record->original_filename, PATHINFO_EXTENSION));
        $downloadName = $baseName.'-FindURSCH.'.$extension;
        $mimeType = $extension === 'pdf'
            ? 'application/pdf'
            : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

        return response()->download($watermarkedPath, $downloadName, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ])->deleteFileAfterSend(true);
    }
}
