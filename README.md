
AtalandaSignature-php
==================

AtalandaSignature-php provides a simple PHP class that lets you sign requests to the [atalogics API](http://atalogics.com) and verify our callbacks.

Installation
============

The best way to install the library is by using [Composer](http://getcomposer.org). Add the following to `composer.json` in the root of your project:

``` javascript
{ 
  "require": {
    "atalanda/signature-php": "1.0.1-beta"
  }
}
```

Then, on the command line:

``` bash
composer install
```

Use the generated `vendor/autoload.php` file to autoload the library classes.

Usage
=====

Signing API calls
-----------------
Use this to add an auth_hash containing a valid signature to the parameter hash that you send to our API.
``` php
$parameters = array(
  "atalogics" => array()
);
$token = new Atalogics\Signature\Token("[Your API key]", "[Your API secret]");
$request = new Atalogics\Signature\Request("POST", "https://atalogics.com/api/order", $parameters);
$signedParameters = $request->sign($token);

var_dump($parameters);
/* => array(2) {
  'atalogics' => array()
  'auth_hash' =>
  array(3) {
    'auth_timestamp' =>
    int(1391167211)
    'auth_key' =>
    string(4) "[Your API key]"
    'auth_signature' =>
    string(64) "552beac4b99949a556b120b7e5f7e22def46f663992a08f0f132ad4afee68b9f"
  }
}*/
```

Verifying the signature of our callbacks
--------------
Use this to verify the signature of our callbacks.
``` php
$data = json_decode($body, true); // convert json from post body into php array
$token = new Atalogics\Signature\Token("[Your API key]", "[Your API secret]");
$request = new Atalogics\Signature\Request("POST", "https://your-server.com/callback", $data);
$signatureCheckResult = $request->authenticate($token);

if($signatureCheckResult["authenticated"] === true) {
  // signature is valid
}
```

