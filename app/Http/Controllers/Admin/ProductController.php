<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{


    public function index(): Response
    {
        $products = Product::with('category','brand','product_images')->get();
        $brands = Brand::get();
        $categories = Category::get();
        return Inertia::render('Admin/Product/Index', ['products' => $products,'brands'=>$brands,'categories'=>$categories]);
    }

    public function store(Request $request): RedirectResponse
    {

        $product = Product::create([
            'title' => $request->title,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
        ]);

        if ($request->hasFile('product_images')) {
            $productImages = $request->file('product_images');

            $uploadedImages = [];

            foreach ($productImages as $image) {
                $uniqueName = time() . '-' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->move('product_images', $uniqueName);
                $uploadedImages[] = 'product_images/' . $uniqueName;
            }


            foreach ($uploadedImages as $uploadedImage) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $uploadedImage,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created Successfully');


    }

    public function deleteImage($id): RedirectResponse
    {

         $image = ProductImage::where('id',$id)->delete();
         return redirect()->route('admin.products.index')->with('success','Image Deleted Successfully');

    }

    public function update(Request $request, $id): RedirectResponse
    {


        $product = Product::findOrFail($id);
        $product->title = $request->title;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->description = $request->description;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;


        if($request->hasFile('product_images')) {
            $productImages = $request->file('product_images');
            $uploadedImages = [];

            foreach ($productImages as $image) {
                $uniqueName = time() . '-' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $destinationPath = 'product_images';


                if (file_exists($destinationPath . '/' . $uniqueName)) {

                    continue;
                }


                $image->move($destinationPath, $uniqueName);
                $uploadedImages[] = 'product_images/' . $uniqueName;
            }

            foreach ($uploadedImages as $uploadedImage) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $uploadedImage,
                ]);
            }
        }
        $product->update();
        return redirect()->back()->with('success','Product updated Successfully');

    }


    public function destroyProduct($id): RedirectResponse
    {
        $product = Product::where('id',$id)->delete();
        return redirect()->route('admin.products.index')->with('success','Product Deleted Successfully');
    }




}
