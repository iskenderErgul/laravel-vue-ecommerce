<?php

namespace App\Helper;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;

class Cart
{
    public static function getCount(): int
    {
        if ($user = auth()->user()) { // kullanıcı kimliği alınır. Eğer giriş yapmışsa
            return CartItem::whereUserId($user->id)->count();  // veritabanında  bu kullanıcının sepetindeki toplam ürün sayısını sayar
        } else {
            return array_reduce(self::getCookieCartItems(), fn ($carry) => $carry + 1, 0);
            //array_reduce fonksiyonu, bir dizi üzerinde bir işlevi uygular ve sonuç olarak tek bir değer döndürür. Bu fonksiyon üç parametre alır:
            //
            //Dizi: İşlemin uygulanacağı dizi.
            //Gerçekleştirilecek işlev: Her bir dizi öğesi üzerinde çalışacak işlev.
            //Başlangıç değeri: İşleme başlamadan önce kullanılacak bir başlangıç değeri.

        }
    }

    public static function getCartItems()
    {
        if ($user = auth()->user()) {// kullanıcı kimliği alınır. Eğer giriş yapmışsa
            return CartItem::whereUserId($user->id)->get()->map(fn (CartItem $item) => ['product_id' => $item->product_id, 'quantity' => $item->quantity]);
            // veritabanında (CartItem modeli kullanılarak) kullanıcının sepetinde bulunan her bir öğenin ürün kimliği (product_id) ve miktarını (quantity) alır.
            // Bu bilgiler, bir dizi olarak dönüştürülür ve döndürülür
        } else {
            return self::getCookieCartItems(); //Eğer kullanıcı giriş yapmamışsa, yani bir misafir kullanıcıysa, sepet bilgileri çerezler aracılığıyla alınır
        }
    }

    public static function getCookieCartItems()
    {
        return json_decode(request()->cookie('cart_items', '[]'), true);
        // Bu, HTTP isteği üzerinden 'cart_items' adlı çerezin değerini alır.
        // Eğer çerez bulunamazsa, varsayılan olarak boş bir dizi ('[]') döndürülür.
        // çerezden alınan değeri JSON formatından PHP dizisine dönüştürür.

    }

    public static function setCookieCartItems(array $cartItems): void
    {
        Cookie::queue('cart_items', json_encode($cartItems), 60*24*30);
        // Bu, çerezde 'cart_items' adıyla verilen sepet öğelerini saklar.
        // Burada 602430 ifadesi, çerezin bir ay (30 gün) boyunca geçerli olacağını belirtir.
        // Çerez, tarayıcı tarafından saklanır ve bu süre sonunda otomatik olarak silinir.
    }

    // Kullanıcının sepetinde veritabanında bulunan öğelerle, çerezlerdeki sepet öğelerini karşılaştırı
    public static function saveCookieCartItems(): void
    {
        $user = auth()->user(); //oturum açmış kullanıcı bilgileri alınır
        $userCartItems = CartItem::where(['user_id' => $user->id])->get()->keyBy('product_id');
        // veritabanından kullanıcıya ait olan sepet bilgilerini alıyoruz
        // bunu ürünün id sine göre diziyoruz

        $savedCartItems = [];
        foreach (self::getCookieCartItems() as $cartItem) { // çerezlerden kullanıcının sepetini alıyoruz ve bunu bir döngüye sokuyoruz.Buradaki amaç aynı ürün olup olmadıgını kontrol etmektir.
            if (isset($userCartItems[$cartItem['product_id']])) { //Eğer kullanıcının sepetinde aynı ürün varsa
                $userCartItems[$cartItem['product_id']]->update(['quantity' => $cartItem['quantity']]); //Miktar güncellenir
                continue;
            }
            // eğer kullanıcının sepetinde bu ürün yoksa
            $savedCartItems[] = [ // bu ürünü eklemek için bir diziye atıyoruz
                'user_id' => $user->id,
                'product_id' => $cartItem['product_id'],
                'quantity' => $cartItem['quantity'],
            ];
        }
        if (!empty($savedCartItems)) { //eğer dizi boş değilse
            CartItem::insert($savedCartItems); // veritabanına olmayan ürün ekleniyor
        }
    }

    public static function moveCartItemsIntoDb(): void
    {
        $request = request();
        $cartItems = self::getCookieCartItems();
        $newCartItems = [];
        foreach ($cartItems as $cartItem) {
            // Veritabanında aynı ürünün ('product_id') ve kullanıcının ('user_id') sepet öğesi olup olmadığı kontrol edilir.
            $existingCartItem = CartItem::where([
                'user_id' => $request->user()->id,
                'product_id' => $cartItem['product_id'],
            ])->first();
            // Eğer zaten bir sepet öğesi bulunuyorsa, bu öğe zaten veritabanında olduğu için $newCartItems dizisine eklenmez ve işlem devam eder.
            if (!$existingCartItem) {
                // Only insert if it doesn't already exist
                $newCartItems[] = [
                    'user_id' => $request->user()->id,
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                ];
            }
        }

        if (!empty($newCartItems)) { // ğer $newCartItems dizisi boş değilse, yani çerezlerde veritabanında olmayan yeni sepet öğeleri bulunuyorsa, bu öğeler veritabanına eklenir
            // Insert the new cart items into the database
            CartItem::insert($newCartItems);
        }
    }

    public static function getProductsAndCartItems()
    {
        $cartItems = self::getCartItems();

        $ids = Arr::pluck($cartItems, 'product_id'); // Bu, sepet öğelerinin ürün kimliklerini (product_id) içeren bir dizi oluşturur
        $products = Product::whereIn('id', $ids)->with('product_images')->get(); // Bu, ürünler tablosunda, daha önce belirlenen ürün kimlikleri listesinde ($ids dizisi) bulunan ürünleri alır
        $cartItems = Arr::keyBy($cartItems, 'product_id'); // Bu, sepet öğelerini bir dizi olarak alır ve ürün kimliğine (product_id) göre dizi elemanlarını indeksler.
        return [$products, $cartItems]; // Son olarak, bu metod, hem ürünlerin hem de sepet öğelerinin birleştirilmiş olarak döndürülmesi gerektiğinden, bu iki bilgiyi bir dizi olarak döndürür
    }
}
