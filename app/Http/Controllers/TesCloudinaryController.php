<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class TesCloudinaryController extends Controller
{
    // Tampilkan form upload
    public function index()
    {
        return view('tes-cloudinary');
    }

    
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048', // maksimal 2MB
        ]);

        $file = $request->file('file');

        if ($file && $file->isValid()) {
            // Hardcode konfigurasi Cloudinary
            $cloudinary = Cloudinary::config([
                'cloud' => [
                    'cloud_name' => 'de2w6wdmv',
                    'api_key'    => '126933779513322',
                    'api_secret' => 'norZMRsQS3uOxNOfeMhunaYl6DE',
                ],
                'url' => [
                    'secure' => true
                ]
            ]);

            // Upload file
            $uploadedFile = Cloudinary::upload($file->getRealPath(), [
                'folder' => 'tes_cloudinary'
            ]);

            $url = $uploadedFile->getSecurePath();

            return redirect()->back()->with('success', "Upload berhasil! URL: $url");
        }

        return redirect()->back()->withErrors('File tidak valid atau belum diunggah.');
    }
}

