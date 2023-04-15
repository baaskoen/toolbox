<?php

namespace App\Http\Controllers;

use App\Http\Requests\LibreConvertRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
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
        $to = $request->get('to');
        $outdir = dirname($file);

        $cmd = "$bin --headless --convert-to $to {$file->getRealPath()} --outdir $outdir";

        $result = shell_exec($cmd);

        $newPath = str_replace('.' . $file->getExtension(), '.' . $to, $file);

        if (!file_exists($newPath)) {
            abort(403, "Error converting file ({$result})");
        }

        return response()->file($newPath)->deleteFileAfterSend();
    }
}
