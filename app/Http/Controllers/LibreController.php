<?php

namespace App\Http\Controllers;

use App\Http\Requests\LibreConvertRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Process;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LibreController extends Controller
{
    /**
     * Convert document (e.g. docx) to e.g. pdf
     *
     * @param LibreConvertRequest $request
     * @return BinaryFileResponse|HttpResponseException
     */
    public function convert(LibreConvertRequest $request): BinaryFileResponse|HttpResponseException
    {
        $bin = config('libre.bin');
        $file = $request->file('document');

        // Move the uploaded file temporarily to storage
        $file->move(storage_path(), $file->getClientOriginalName());
        $movedPath = storage_path($file->getClientOriginalName());

        $to = $request->get('to');
        $outdir = storage_path();
        $cmd = "$bin --headless --convert-to $to {$movedPath} --outdir $outdir";
        $result = Process::run($cmd);

        // Replace the file extension with the `to` extension
        $newPath = str_replace('.' . $file->getClientOriginalExtension(), '.' . $to, $movedPath);

        // Remove the uploaded file
        unlink($movedPath);

        if (!file_exists($newPath)) {
            logger($result->errorOutput());
            abort(403, "Error converting file ({$result->errorOutput()})");
        }

        return response()->file($newPath)->deleteFileAfterSend();
    }
}
