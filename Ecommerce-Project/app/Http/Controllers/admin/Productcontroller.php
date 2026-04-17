<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image; // Version 2.7 এর জন্য এটিই সঠিক
use App\Models\ProductImage;

class Productcontroller extends Controller
{
    
    public function index() {
        $products = Product::orderBy('created_at', 'DESC')->get();
        return response()->json([
            'status' => 200,
            'data' => $products
        ], 200);
    }

    
    public function store(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'price' => 'required|numeric',
            'category' => 'required|integer',
            'sku' => 'required|unique:products,sku',
            'is_featured' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        
        $product = new Product();
        $product->title = $request->title;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->category_id = $request->category;
        $product->brand_id = $request->brand;
        $product->sku = $request->sku;
        $product->qty = $request->qty;
        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->status = $request->status;
        $product->is_featured = $request->is_featured;
        $product->barcode = $request->barcode;
        $product->save();


        if (!empty($request->gallery)) {
            
            foreach ($request->gallery as $key => $tempImageId) {
                $tempImage = TempImage::find($tempImageId);

                if ($tempImage) {
                    $ext = pathinfo($tempImage->name, PATHINFO_EXTENSION);
                    $imageName = $product->id . '-' . time() . '-' . $key . '.' . $ext;
                    
                    $sourcePath = public_path('uploads/temp/' . $tempImage->name);
                    $largePath = public_path('uploads/products/large/' . $imageName);
                    $smallPath = public_path('uploads/products/small/' . $imageName);


                    if (!File::exists(public_path('uploads/products/large'))) {
                File::makeDirectory(public_path('uploads/products/large'), 0775, true);
            }

                    if (File::exists($sourcePath)) {
                        
                        // Large Image: Resize (1200px width, auto height, aspect ratio maintain)
                        Image::make($sourcePath)->resize(1200, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })->save($largePath);

                        // Small Image: Fit/Crop (400x460)
                        Image::make($sourcePath)->fit(400, 460)->save($smallPath);


                        /* $productImage = new ProductImage();
                         $productImage->image=$imageName;
                         $productImage->product_id = $product_id;
                         $productImage->save();*/

                         $productImage = new ProductImage();
                         // $productImage->image = $imageName;
                         $productImage->product_id = $product->id; 
                         $productImage->image = $imageName;      
                         $productImage->save();
                
                        if ($key == 0) {
                            $product->image = $imageName;
                            $product->save();
                        }
                    }
                }
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product has been created successfully'
        ], 200);
    }

    
    public function show($id) {
        $product = Product::find($id);
        if ($product == null) {
            return response()->json(['status' => 404, 'message' => 'Product not found'], 404);
        }
        return response()->json(['status' => 200, 'data' => $product], 200);
    }

    
    public function update($id, Request $request) {
        $product = Product::find($id);
        if ($product == null) {
            return response()->json(['status' => 404, 'message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'price' => 'required|numeric',
            'category' => 'required|integer',
            'sku' => 'required|unique:products,sku,' . $id . ',id',
            'is_featured' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'errors' => $validator->errors()], 400);
        }

        $product->title = $request->title;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->category_id = $request->category;
        $product->brand_id = $request->brand;
        $product->sku = $request->sku;
        $product->qty = $request->qty;
        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->status = $request->status;
        $product->is_featured = $request->is_featured;
        $product->barcode = $request->barcode;
        $product->save();

        return response()->json(['status' => 200, 'message' => 'Product updated successfully'], 200);
    }


    public function destroy($id) {
        $product = Product::find($id);
        if ($product == null) {
            return response()->json(['status' => 404, 'message' => 'Product not found'], 404);
        }

        
        if (!empty($product->image)) {
            File::delete(public_path('uploads/products/large/' . $product->image));
            File::delete(public_path('uploads/products/small/' . $product->image));
        }

        $product->delete();
        return response()->json(['status' => 200, 'message' => 'Product deleted successfully'], 200);
    }
}