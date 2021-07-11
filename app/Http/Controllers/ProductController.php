<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer'],
            'price' => ['required', 'numeric'],
        ]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // If file does not exist
        if (!Storage::disk('local')->exists('products.json')) {
            $products = [];
            $gross_total = 0;
            return view('home', ['products' => $products, 'gross_total' => $gross_total]);
        }

        $products = Storage::get('products.json');
        $products = json_decode($products);

        $gross_total = 0;

        // Adds the total from the JSON array
        foreach ($products as $product) {
            $gross_total += $product->total;
        }

        // Sends the products array to the main page
        return view('home', ['products' => $products, 'gross_total' => $gross_total]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $name = $request->name;
        $quantity = (int)$request->quantity;
        $price = (int)$request->price;

        $product = array(
            'name' => $name,
            'quantity' => $quantity,
            'price' => $price,
            'created_at' => date("Y-m-d H:i:s"),
            'total' => ($price * $quantity)
        );
        $created_product = $product;

        if (!Storage::disk('local')->exists('products.json')) {
            $product = json_encode(array($product));
            Storage::put('products.json', $product);

            $created_product['id'] = 0;

            return response()->json($created_product);
    
        } else {
            $products = Storage::get('products.json');
            $products = json_decode($products);

            array_push($products, $product);

            $products = json_encode($products);

            Storage::put('products.json', $products);
            $products = Storage::get('products.json');
            $products = json_decode($products);
            $product['id'] = count($products) - 1;
    
            return response()->json($product);
    
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
        $products = Storage::get('products.json');
        $products = json_decode($products);
        $id = $request->id;

        $products[$id]->name = $request->name;
        $products[$id]->quantity = (int)$request->quantity;
        $products[$id]->price = (int)$request->price;
        $products[$id]->total = ((int)$request->quantity * (int)$request->price);

        $products = json_encode($products);

        Storage::put('products.json', $products);

        return response([
            "id" => $request->id,
            "name" => $request->name,
            "quantity" => $request->quantity,
            "price" => $request->price,
            'total' => ((int)$request->quantity * (int)$request->price)
        ], 200);
    }
}
