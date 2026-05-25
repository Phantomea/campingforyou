<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class GalleryController extends Controller
{
    public function index(): JsonResponse
    {
        $galleryPath = env('GALLERY_PATH', base_path('../frontend/public/gallery'));

        if (! File::isDirectory($galleryPath)) {
            return response()->json([]);
        }

        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

        $files = collect(File::files($galleryPath))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), $extensions)
                && ! str_starts_with($file->getFilename(), 'hero-'))
            ->sortBy(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'filename' => $file->getFilename(),
                'url'      => '/gallery/' . $file->getFilename(),
            ])
            ->values();

        return response()->json($files);
    }
}
