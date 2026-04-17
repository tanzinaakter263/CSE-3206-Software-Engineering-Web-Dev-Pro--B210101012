<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image; // Version 2.7 এর জন্য এটিই সঠিক

class TempImageController extends Controller
{
    /**
     * This method will save a temporary image and return its ID
     */
    public function store(Request $request)
    {
    
        $image = $request->file('image');

        if (!empty($image)) {
            
            $ext = $image->getClientOriginalExtension();
            $newName = time() . '.' . $ext;

            
            $tempImage = new TempImage();
            $tempImage->name = $newName;
            $tempImage->save();

        
            $tempPath = public_path('/uploads/temp');
            $thumbPath = public_path('/uploads/temp/thumb');

            if (!File::exists($tempPath)) {
                File::makeDirectory($tempPath, 0775, true);
            }
            if (!File::exists($thumbPath)) {
                File::makeDirectory($thumbPath, 0775, true);
            }

            
            $image->move($tempPath, $newName);

            
            $sourcePath = $tempPath . '/' . $newName;
            $destPath = $thumbPath . '/' . $newName;

            
            Image::make($sourcePath)
                ->fit(300, 275)
                ->save($destPath);

            
            return response()->json([
                'status' => 200,
                'image_id' => $tempImage->id,
                'name' => $newName,
                'message' => 'Image uploaded successfully'
            ], 200);
        }

        return response()->json([
            'status' => 400,
            'message' => 'No image provided'
        ], 400);
    }
}
