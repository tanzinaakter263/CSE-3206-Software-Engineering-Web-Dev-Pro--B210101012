<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image; 
use App\Models\ProductImage;

class Productcontroller extends Controller
{
    
    public function index() {
        $products = Product::orderBy('created_at', 'DESC')
        ->with(['product_images','product_sizes'])
        
        ->get();
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


        if(!empty($request->sizes)){
            
            foreach ($request->sizes as $sizeId){


                $productSize =new ProductSize();
                $productSize->size_id = $sizeId;
                $productSize->product_id=$product->id;
                $productSize->save();
            }
        }


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
        $product = Product::with(['product_images','product_sizes'])
        ->find($id);
        if ($product == null) {
            return response()->json(['status' => 404, 'message' => 'Product not found'], 404);
        }

        $productSizes = $product->product_sizes()->pluck('size_id');


        return response()->json([
            'status' => 200, 
            'data' => $product,
             'productSizes' =>$productSizes
            ], 200);
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
        if ($request->hasFile('image')) {
        $imageName = time().'.'.$request->image->extension();
        $request->image->move(public_path('uploads/products'), $imageName);

        $product->image = $imageName;
    }
        $product->save();


        if(!empty($request->sizes)){
            ProductSize::where('product_id', $product->id)->delete();
            foreach ($request->sizes as $sizeId){


                $productSize =new ProductSize();
                $productSize->size_id = $sizeId;
                $productSize->product_id=$product->id;
                $productSize->save();
            }
        }
          
        return response()->json(['status' => 200, 'message' => 'Product updated successfully'], 200);
    }


  public function destroy($id) {
        $product = Product::with('product_images')->find($id);

        if (empty($product)) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found'
            ], 404);
        }

        
        if (!empty($product->image)) {
            $largePath = public_path('uploads/products/large/' . $product->image);
            $smallPath = public_path('uploads/products/small/' . $product->image);

            if (File::exists($largePath)) File::delete($largePath);
            if (File::exists($smallPath)) File::delete($smallPath);
        }

        
        $productImages = ProductImage::where('product_id', $id)->get();
        if ($productImages->isNotEmpty()) {
            foreach ($productImages as $productImage) {
                $largeGalleryPath = public_path('uploads/products/large/' . $productImage->image);
                $smallGalleryPath = public_path('uploads/products/small/' . $productImage->image);

                if (File::exists($largeGalleryPath)) 
                    {
                        File::delete($largeGalleryPath);
                    }
                if (File::exists($smallGalleryPath))
                    {
                         File::delete($smallGalleryPath);
                    }
                
                ProductImage:: where('id',$productImage->id)->delete();
            }
        }

        $product->delete();
        if($product->product_images){
            foreach ($product->product_images as $productImage){
                 $largePath = public_path('uploads/products/large/'.$productImage->image);
    $smallPath = public_path('uploads/products/small/'.$productImage->image);

    if(File::exists($largePath)){
        File::delete($largePath);
    }
    if(File::exists($smallPath)){
        File::delete($smallPath);
    }
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product and all thumbnails deleted successfully'
        ], 200);
    }

    public function saveProductImage(Request $request) {
        
        if (!$request->hasFile('image')) {
            return response()->json(['status' => 400, 'message' => 'No image uploaded'], 400);
        }

        $image = $request->file('image');
        $ext = $image->getClientOriginalExtension();
        $sourcePath = $image->getRealPath(); 

        
        $productImage = new ProductImage();
        $productImage->product_id = $request->product_id;
        $productImage->image = 'NULL'; 
        $productImage->save();

        $imageName = $productImage->id . '-' . time() . '.' . $ext;
        $productImage->image = $imageName;
        $productImage->save();

       
        $largeDir = public_path('uploads/products/large/');
        $smallDir = public_path('uploads/products/small/');

        if (!File::exists($largeDir)) File::makeDirectory($largeDir, 0775, true);
        if (!File::exists($smallDir)) File::makeDirectory($smallDir, 0775, true);

        // Large Thumbnail
        Image::make($sourcePath)->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save($largeDir . $imageName);

        // Small Thumbnail
        Image::make($sourcePath)->fit(400, 460)->save($smallDir . $imageName);

        return response()->json([
            'status' => 200,
            'image_id' => $productImage->id,
            'imagePath' => $imageName,
            'message' => 'Image saved with thumbnails'
        ], 200);
    }
   public function updateDefaultImage(Request $request ){
    //return 
    //response()->json($request->all());

    $product = Product::find($request->product_id);
    $product->image = $request->image;
    $product->save();

    
    return response()->json([
        'status' =>200,
        'message' =>'Product default image changed successfully',
    ],200);
   }

public function deleteProductImage($id){
    $productImage = ProductImage::find($id);
    if($productImage ==null){
return response()->json([
        'status' =>404,
        'message' =>'Image Not Found',
    ],404);
    }

    $largePath = public_path('uploads/products/large/'.$productImage->image);
    $smallPath = public_path('uploads/products/small/'.$productImage->image);

    if(File::exists($largePath)){
        File::delete($largePath);
    }
    if(File::exists($smallPath)){
        File::delete($smallPath);
    }

    
    $productImage->delete();
    return response()->json([
        'status' =>200,
        'message' =>'Product  image deleted successfully',
    ],200);
}


}




   