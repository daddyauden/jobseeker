<?php

namespace jobseeker\Bundle\ToolBundle\Service\Maxmind;

class MaxMind
{

    private static $country = array(
        "code" => "geoip_country_code_by_addr",
        "name" => "geoip_country_name_by_addr"
    );
    private static $city = array(
        "countryCode" => "country_code",
        "countryName" => "country_name",
        "regionCode" => "region",
        "city" => "city",
        "postalCode" => "postal_code",
        "lat" => "latitude",
        "lng" => "longitude",
        "metroCode" => "metro_code",
        "areaCode" => "area_code",
        "continentCode" => "continent_code"
    );

    public static function getCountry($ip, $by = "all")
    {
        include_once __DIR__ . DIRECTORY_SEPARATOR . "MaxMindCountry.php";
        $db = __DIR__ . "/country.dat";
        $gi = geoip_open($db, GEOIP_STANDARD);
        if ($by === "all") {
            $country = array();
            foreach (self::$country as $key => $method) {
                $country[$key] = $method($gi, $ip);
            }
            return $country;
        }
        $method = array_key_exists($by, self::$country) ? self::$country[$by] : self::$country["code"];
        return $method($gi, $ip);
        geoip_close($gi);
    }

    public static function getCity($ip, $by = "all")
    {
        include_once __DIR__ . DIRECTORY_SEPARATOR . "MaxMindCity.php";
        $db = __DIR__ . "/city.dat";
        $gi = geoip_open($db, GEOIP_STANDARD);
        $city = geoip_record_by_addr($gi, $ip);
        $method = array_key_exists($by, self::$city) ? self::$city[$by] : self::$city["city"];
        if ($by === "all") {
            $citys = array();
            foreach (self::$city as $key => $method) {
                $citys[$key] = $city->$method;
            }
            if ($city->region) {
                $citys['regionName'] = $GEOIP_REGION_NAME[$city->country_code][$city->region];
            }
            return $citys;
        }
        if ($by === "regionName" && $city->region) {
            return $GEOIP_REGION_NAME[$city->country_code][$city->region];
        } else {
            return $city->$method;
        }
        geoip_close($gi);
    }

}
