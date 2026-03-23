<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicStorageController extends Controller
{
    public function show(Request $request, string $path)
    {
        $path = ltrim($path, '/');

        if ($path === '' || Str::contains($path, '..') || Str::contains($path, "\0")) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (config('filesystems.disks.public.driver') !== 'local') {
            return redirect()->away($disk->url($path));
        }

        if (!$disk->exists($path)) {
            abort(404);
        }

        $absolutePath = $disk->path($path);

        if (!is_file($absolutePath)) {
            abort(404);
        }

        return response()->file($absolutePath, [
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
