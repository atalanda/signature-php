<?php
/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class AtalogicsSignatureTest extends PHPUnit_Framework_TestCase {
  
  public function setUp() {
    parent::setUp();

    $this->token = new Atalogics\Signature\Token("d8b3535d218eeab8fc1b6df31a355105512212bf448da3322242c0ddd65b513e", "caf30f900ab4b414111ea12d007fc647598bb58ff9d2377aa1522238e2f13a63");
  }

  public function callPrivateMethod($object, $methodName)
  {
    $reflectionClass = new \ReflectionClass($object);
    $reflectionMethod = $reflectionClass->getMethod($methodName);
    $reflectionMethod->setAccessible(true);

    $params = array_slice(func_get_args(), 2); //get all the parameters after $methodName
    return $reflectionMethod->invokeArgs($object, $params);
  }

  public function testParamBuildingOrder() {
    $params = array(
      "atalogics" => array(
        "api_key" => "abcdefg",
        "catch" => array(
          "name" => "Max Musermann",
          "street" => "Getreidegasse 20",
          "postal_code" => "5020",
          "city" => "Salzburg",
          "phone_number" => "123456",
          "email" => "max@mustermann.de",
          "city_service_code" => "TIMESLOT",
          "city_service_time_slot_id" => "26"
        ),
        "drop" => array(
          "name" => "Marta Musterfrau",
          "street" => "Getreidegasse 76",
          "postal_code" => "5020",
          "city" => "Salzburg",
          "phone_number" => "435236",
          "email" => "marta@musterfrau.de",
          "city_service_code" => "TIMESLOT",
          "city_service_time_slot_id" => "30"
        )
      )
    );

    $request = new Atalogics\Signature\Request("POST", "/api/order", $params);
    $result = $this->callPrivateMethod($request, "buildParameterString");
    $expected = "POST/api/orderatalogicsapi_keyabcdefgcatchcitySalzburgcity_service_codeTIMESLOTcity_service_time_slot_id26emailmax@mustermann.denameMax Musermannphone_number123456postal_code5020streetGetreidegasse 20dropcitySalzburgcity_service_codeTIMESLOTcity_service_time_slot_id30emailmarta@musterfrau.denameMarta Musterfrauphone_number435236postal_code5020streetGetreidegasse 76";
    $this->assertEquals($expected, $result);
  }

  public function testParamBuildingDeliveryOffers() {
    $params = array(
      "atalogics" => array(
        "api_key" => "abcdefg",
        "catch" => array(
          "name" => "Max Mustermann",
          "street" => "Getreidegasse 27",
          "postal_code" => "5020",
          "city" => "Salzburg",
          "time" => array(
            "workday" => "08:00-12:00,14:00-20:00", // since it's monday only this should apply
            "saturday" => "08:00-19:00",
            "holiday" => false,
            "tuesday" => false,
            "half_holiday" => "08:00-12:00",
            "exclude_dates" => [
              "12/23/2014",
              "12/24/2014"
            ]
          )
        ),
        "drop" => array(
          "name" => "Marta Musterfrau",
          "street" => "Auerspergerstr. 41",
          "postal_code" => "5020",
          "city" => "Salzburg",
          "extra_services" => ["R18"] // hier gleich extra services mitbuchen
        )
      )
    );

    $request = new Atalogics\Signature\Request("POST", "/api/deliveryOffers", $params);
    $result = $this->callPrivateMethod($request, "buildParameterString");
    $expected = "POST/api/deliveryOffersatalogicsapi_keyabcdefgcatchcitySalzburgnameMax Mustermannpostal_code5020streetGetreidegasse 27timeexclude_dates12/23/201412/24/2014half_holiday08:00-12:00holidayfalsesaturday08:00-19:00tuesdayfalseworkday08:00-12:00,14:00-20:00dropcitySalzburgextra_servicesR18nameMarta Musterfraupostal_code5020streetAuerspergerstr. 41";
    $this->assertEquals($expected, $result);
  }

  public function testParamBuildingScrambled() {
    $params1 = array(
      "atalogics" => array(
        "api_key" => "abcdefg",
        "catch" => array(
          "name" => "Max Mustermann",
          "street" => "Getreidegasse 27",
          "postal_code" => "5020",
          "city" => "Salzburg",
          "time" => array(
            "workday" => "08:00-12:00,14:00-20:00", // since it's monday only this should apply
            "saturday" => "08:00-19:00",
            "holiday" => false,
            "tuesday" => false,
            "half_holiday" => "08:00-12:00",
            "exclude_dates" => [
              "12/23/2014",
              "12/24/2014"
            ]
          )
        ),
        "drop" => array(
          "name" => "Marta Musterfrau",
          "street" => "Auerspergerstr. 41",
          "postal_code" => "5020",
          "city" => "Salzburg",
          "extra_services" => ["R18"] // hier gleich extra services mitbuchen
        )
      )
    );

    $request = new Atalogics\Signature\Request("POST", "/api/deliveryOffers", $params1);
    $result = $this->callPrivateMethod($request, "buildParameterString");

    $params2 = array(
      "atalogics" => array(
        "drop" => array(
          "street" => "Auerspergerstr. 41",
          "extra_services" => ["R18"], // hier gleich extra services mitbuchen
          "postal_code" => "5020",
          "city" => "Salzburg",
          "name" => "Marta Musterfrau"
        ),
        "catch" => array(
          "street" => "Getreidegasse 27",
          "time" => array(
            "workday" => "08:00-12:00,14:00-20:00", // since it's monday only this should apply
            "holiday" => false,
            "saturday" => "08:00-19:00",
            "half_holiday" => "08:00-12:00",
            "exclude_dates" => [
              "12/23/2014",
              "12/24/2014"
            ],
            "tuesday" => false
          ),
          "postal_code" => "5020",
          "name" => "Max Mustermann",
          "city" => "Salzburg",
        ),
        "api_key" => "abcdefg"
      )
    );

    $request2 = new Atalogics\Signature\Request("POST", "/api/deliveryOffers", $params2);
    $result2 = $this->callPrivateMethod($request2, "buildParameterString");
    $this->assertEquals($result2, $result);
  }

  public function testSign() {
    $time = 1391089574;

    $request = new Atalogics\Signature\Request("POST", "/api/somePath", array("foo" => "bar"), $time);
    $parametersWithAuthHash = $request->sign($this->token);
    $authParams = $parametersWithAuthHash["auth_hash"];
    $this->assertEquals($time, $authParams["auth_timestamp"]);
    $this->assertEquals($this->token->getKey(), $authParams["auth_key"]);

    $expectedSignature = hash("sha256", "POST/api/somePathfoobar".$this->token->getKey().$this->token->getSecret().$time);
    $this->assertEquals($expectedSignature, $authParams["auth_signature"]);
  }

  public function testVerifySignatureSuccess() {
    $time = 1391089574;
    $request = new Atalogics\Signature\Request("POST", "/api/somePath", array("foo" => "bar"), $time);
    // create auth_hash
    $authParams = $request->sign($this->token)["auth_hash"];

    $request2 = new Atalogics\Signature\Request("POST", "/api/somePath", array(
      "foo" => "bar",
      "auth_hash" => $authParams
    ), $time);
    $this->assertEquals(array("authenticated" => true), $request2->authenticate($this->token));
  }

  public function testVerifySignatureFailDifferentContent() {
    $time = 1391089574;
    $request = new Atalogics\Signature\Request("POST", "/api/somePath", array("foo" => "bar"), $time);
    // create auth_hash
    $authParams = $request->sign($this->token)["auth_hash"];

    $request2 = new Atalogics\Signature\Request("POST", "/api/somePath", array(
      "foo" => "barDIFFERENT",
      "auth_hash" => $authParams
    ), $time);
    $this->assertEquals(array("authenticated" => false, "reason" => "Signature does not match"), $request2->authenticate($this->token));
  }

  public function testVerifySignatureFailTooOld() {
    $time = 1391089574;
    $request = new Atalogics\Signature\Request("POST", "/api/somePath", array("foo" => "bar"), $time);
    // create auth_hash
    $authParams = $request->sign($this->token)["auth_hash"];

    $timestampGrace = 600;
    $timeLater = $time + $timestampGrace + 1;
    $request2 = new Atalogics\Signature\Request("POST", "/api/somePath", array(
      "foo" => "bar",
      "auth_hash" => $authParams
    ), $timeLater);
    $this->assertEquals(array(
      "authenticated" => false,
      "reason" => "Auth timestamp is older than ".$timestampGrace." seconds"
    ), $request2->authenticate($this->token, $timestampGrace));
  }

  public function testVerifySignatureFailNoAuthHash() {
    $time = 1391089574;
    $request = new Atalogics\Signature\Request("POST", "/api/somePath", array(
      "foo" => "bar"
    ), $time);
    $this->assertEquals(array(
      "authenticated" => false, 
      "reason" => "Auth hash is missing"
    ), $request->authenticate($this->token));
  }
}