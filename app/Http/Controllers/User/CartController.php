<?php

namespace App\Http\Controllers\User;

use App\Helper\Cart;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function view(Request $request , Product $product) {

        $user = $request->user();

        if ($user)
        {
            $cartItems = CartItem::where('user_id',$user->id)->get();
            $userAddress = UserAddress::where('user_id',$user->id)->where('isMain',1)->first();
            if ($cartItems->count() > 0)
            {
                return Inertia::render('User/CartList',
                [
                    'cartItems' => $cartItems ,
                    'userAddress' => $userAddress
                ]);
            }

        }else {
            $cartItems = Cart::getCookieCartItems();
            if (count($cartItems)  > 0 )
            {
                $cartItems = new CartResource(Cart::getProductsAndCartItems());
                return Inertia::render('User/CartList', ['cartItems' => $cartItems]);
            }else {
                return redirect()->back();
            }
        }



    }
    public function store(Request $request, Product $product): RedirectResponse
    {
        // kullanıcıdan ürün miktarını alınır. eğer ürün miktarı gelmesse varsayılan olarak 1 atarır.
        $quantity = $request->post('quantity',1);

        //kullanıcının giriş yapıp yapmadığı kontrol edilir
        $user = $request->user();


        if($user) {//Eğer kullanıcı giriş yapmışsa,

            //  o zaman veritabanında(CartItem modeli kullanılarak) bu kullanıcının sepetinde bu ürünün olup olmadığını kontrol eder
            $cartItem = CartItem::where(['user_id' => $user->id , 'product_id' => $product->id])->first();

            if ($cartItem) {  //Eğer ürün zaten sepette varsa, miktarını artırır
                $cartItem->increment('quantity');
            }else {  //Eğer ürün sepette yoksa, yeni bir kart öğesi oluşturur
                CartItem:: create([
                    'user_id'=> $user->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                ]);
            }

        }else { //Eğer kullanıcı giriş yapmamışsa

            $cartItems = Cart::getCookieCartItems(); //Sepet bilgilerini cookiler ile alırız
            $isProductExists = false ;
            foreach ($cartItems  as $item) {
                if ($item['product_id']===$product->id)  // Daha sonra bu ürünün eklenip eklenmediği kontrol edilir
                {
                    $item['quantity'] += $quantity; // eğer eklenmişşe ürünün miktarı arttırılır
                    $isProductExists = true;
                    break;

                }
            }

            if (!$isProductExists) //  Eğer eklenmemişse, yeni bir sepet öğesi oluşturur
            {
                $cartItems[] =[
                    'user_id' => null,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    ];
            }
            Cart::setCookieCartItems($cartItems); //$cartItems değişkeninde bulunanları çereze kaydediyor
        }

        return redirect()->back()->with('success','cart added successfully');



    }
    public  function update(Request $request, Product $product): RedirectResponse
    {

        $quantity = $request->integer('quantity'); //miktar bilgisini aldık
        $user = $request->user();
        if ($user) { // eğer kullanıcı varsa
            //  o zaman veritabanında (CartItem modeli kullanılarak) bu kullanıcının sepetindeki belirli bir ürünün miktarını  gelen miktar ile günceller
            CartItem::where(['user_id' => $user->id, 'product_id' => $product->id])->update(['quantity' => $quantity]);
        } else {  //  eğer kullanıcı giriş yapmamışsa
            $cartItems = Cart::getCookieCartItems();  //  sepet bilgileri çerezler aracılığıyla alınır
            foreach ($cartItems as &$item) {  //  Eğer ürün sepete daha önce eklenmişse, ilgili ürünün miktarını günceller
                if ($item['product_id'] === $product->id) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }
            Cart::setCookieCartItems($cartItems);   //  Güncellenmiş sepet bilgileri çerezlere kaydedilir
        }

        return redirect()->back();

    }
    public function  delete(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            CartItem::query()->where(['user_id' => $user->id, 'product_id' => $product->id])->first()?->delete();
            if (CartItem::count() <= 0) {
                return redirect()->route('home')->with('info', 'your cart is empty');
            } else {
                return redirect()->back()->with('success', 'item removed successfully');
            }
        } else {
            $cartItems = Cart::getCookieCartItems();
            foreach ($cartItems as $i => &$item) {
                if ($item['product_id'] === $product->id) {
                    array_splice($cartItems, $i, 1);
                    break;
                }
            }
            Cart::setCookieCartItems($cartItems);
            if (count($cartItems) <= 0) {
                return redirect()->route('home')->with('info', 'your cart is empty');
            } else {
                return redirect()->back()->with('success', 'item removed successfully');
            }
        }
    }
}
