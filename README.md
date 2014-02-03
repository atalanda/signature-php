
AtalandaSignature-php
==================

AtalandaSignature-php provides a simple PHP class that lets you sign requests to the [atalogics API](http://atalogics.com) and verify our callbacks.

Installation
============

The best way to install the library is by using [Composer](http://getcomposer.org). Add the following to `composer.json` in the root of your project:

``` javascript
{ 
  "require": {
    "atalanda/signature-php": "dev-master"
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
Use this to add an auth_hash containing a valid signature to the parameter hash that you send to our api.
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

**Example**
> POST Request to https://atalogics.com/api/orderOffer with the following JSON:
``` javascript
{
  "atalogics": {
    "api_key": "5f70fd232454e5c142566dbacc3dec5",
    "offer_id": "33/2014-01-22/1/2014-01-22",
    "expected_fee": 5.59,
    "external_id": "AZDF-234",
    "url_state_update": "https://ihr-server.de/atalogics/callbacks",
    "catch": {
        "name": "Top Fashion Shop",
        "street": "Schneiderstraße 20",
        "postal_code": "5020",
        "city": "Salzburg",
        "phone_number": "123456",
        "email": "info@fashionshop.de"
    },
    "drop": {
        "name": "Marta Musterkundin",
        "street": "Kaufstr. 76",
        "postal_code": "5020",
        "city": "Salzburg",
        "phone_number": "435236",
        "email": "marta@musterkundin.de",
        "extra_services": ["R18"]
    }
  }
}
```
``` php
$token = new Atalogics\Signature\Token("[Your API key]", "[Your API secret]");
$request = new Atalogics\Signature\Request("POST", "https://atalogics.com/api/orderOffer", $parameters); //  parameters contains a hash representing the json above
$signedParameters = $request->sign($token);
// Now send a post request to our api and set the body to the json encoded version of $signed_parameters
```
If you do a GET Request, you also have to sign all URL parameters. Simply include them in the parameters hash. Send the produced auth parameters along with the other URL parameters, for example:
> https://atalogics.com/api/status?tracking_id=42ef32a&api_key=abcde**&auth_signature=ab332d2f&auth_timestamp=123244&auth_key=abcde**

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

