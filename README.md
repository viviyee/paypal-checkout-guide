Paypal Checkout Integration

## Getting API Credentials

1. Go to https://developer.paypal.com/home. At the top right corner, you'll see __Log Into Dashboard__ button, click it to sign in. If you're already signed in, you'll see your account name instead.

2. Go to __Developer Dashboard__. Select __My Apps & Credentials__ from sidebar. Create an __app__ (Sandbox for testing, Live for live). You'll get __CLIENT ID__ and __SECRET__ from the __app__ you created.

3. Now go to your laravel project, open __.env__ file and add these.
```
PAYPAL_ENV=testing
PAYPAL_CLIENT_ID=(your paypal CLIENT ID)
PAYPAL_SECRET=(your paypal SECRET)
```

## Client-side Integration

OK, so you have a page, you want the user to see Paypal buttons and make payments on that page. you want to integrate Paypal Checkout on that page. Let's call that page __paypal payment page__ for the sake of this guide.

1. Add this script on the __paypal payment page__ in the head section of html.
```html
<script src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_CLIENT_ID') }}"></script>
```

2. Add this div on the __paypal payment page__ in the body section of html.
```html
<div id="paypal-button-container"></div>
```

3. Download __paypal.js__ script and include it on the __paypal payment page__ in the body section of html, before the closing body tag _</body>_. Now you see Paypal buttons on the __paypal payment page__. 

## Server-side Integration

Let's try to understand Paypal Checkout a bit. Paypal Checkout takes __3__ stages to make a successful payment. They are STAGE 1: __create order__, STAGE 2: __approve order__, and STAGE 3: __capture order__.

#### STAGE 1: create order

When a user click on Paypal button, we want to __create an order__ and pop up a browser window for Paypal login.

1. Download __PaypalController__ file and put it in __App\Http\Controllers__ folder of your Laravel project.

2. If you don't have __GuzzleHttp__ installed in your project, install it.
```sh
composer require guzzlehttp/guzzle
```

3. Add these routes in __web.php__ file.
```php
Route::get('/paypal/orders/{id}', 'PaypalController@create_order');
Route::get('/paypal/orders/{order_id}/capture', 'PaypalController@capture_order');
Route::get('/paypal/orders/{order_id}/show', 'PaypalController@show_order');
```

4. In __PaypalController__, _create_order_ function, you need to modify code a little bit. When you look at the route for that function, there is a required parameter __id__. For example, you want to make a payment for a __product__, you need to pass __product id__ from frontend.

5. Get __product__ by querying from your database. Then pass values into _get_purchase_details_ function. This is an example -
```php
$product = Product::find($id);
$purchase_details = $this->get_purchase_details($product->value, $product->currency, $product->name, $product->details);
```

6. _get_purchase_details_ function takes __4__ arguments (value, currency, name, details). So make sure you pass these arguments correctly.

7. You also need to pass that __product id__ from frontend. Modify code in __paypal.js__. When you look at the file, you'll see _createOrder_ function pass __id__ to the route. ```fetch(`/paypal/orders/${id}`)``` So get __product id__ beforehand

#### STAGE 2: approve order

When the brower window popped up and the user enter his paypal email and password correctly, and click pay, the __order will be approved__.

#### STAGE 3: capture order

When you look at __paypal.js__ file, you'll see _onApprove_ function. When the __order is approved__, it will fetch to __capture order__ automatically. 

When it's successfully captured, it will return an __order__ from _fetch_ function. You can console.log it and see the __order object__ yourself. In that object, you'll see __id__ property. That's __id of the order__. If you want to see the __details of the order__ later, use this route and pass the __order id__.
```php
/paypal/orders/{order_id}/show
````

Another important property from the return __order__ oject, is __purchase_units__ property. ```purchase_units[0].payments.captures[0].id``` is the __Transaction id__. 

## Going LIVE
Go to https://developer.paypal.com/home, Dashboard, My Apps & Credentials, create a __Live app__, take __CLIENT ID__ and __SECRET__, and replace those values in __.env__ file. Change ```PAYPAL_ENV=LIVE```.

