<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \GeniusTS\PrayerTimes\Prayer;
use \GeniusTS\PrayerTimes\Coordinates;
use \GeniusTS\PrayerTimes\Times;

use App\Helpers\PrayerTimes\Methods\Kemenag;

class PrayerTimesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    protected function get_if_exists($arr, $key)
    {
        if(array_key_exists($key, $arr))
        {
            return $arr[$key];
        }
        return NULL;
    }

    /**
     * Calculate Prayer Times according to the parameter sepecified
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function calculate(Request $request)
    {
         /**
         * Format of request body:
         * {
         *  'lat': <latitude point, WGS84>
         *  'lng': <longitude point, WGS84>
         *  'elevation': <elevation point. Leave empty for default.>
         *  'method': <Method key name> {'world', 'karachi', 'etc'}
         *  'asr_method': <Asr method to resolve> {'standard', 'hanafi'}
         *  'high_lat': <High Altitude method to resolve> {'middle_of_night', 'seventh_of_night', 'twilight_angle'}
         *  'adjustments' <dict of adjustment in minutes> {'fajr':<val>,'asr':<val>, etc}, value in minutes
         *  'date': <Date of format: 'YYYY-MM-DD'>
         *  'timezone': <timezone offset, in integer of hours offset>
         *  'time_format': <datetime format string of return type>
         * }
         */
        $args = json_decode($request->getContent(), true);
        $lat = $args["lat"];
        $lng = $args["lng"];
        $date = $args["date"];
        $timezone = $args["timezone"];
        $time_format = $args["time_format"];
        $method = self::get_if_exists($args, "method");
        $high_lat = self::get_if_exists($args, "high_lat");
        $asr_method = self::get_if_exists($args, "asr_method");
        $adjustments = self::get_if_exists($args, "adjustments");

        // Instantiate coordinates
        $coords = new Coordinates($lng, $lat);

        // Instantiate Prayer algs
        $prayer_times = new Prayer($coords);

        // Instantiate method params
        switch($method)
        {
            case "kemenag":
                $prayer_times->setMethod(new Kemenag());
                break;
            default:
                $prayer_times->setMethod($method);
        }
        
        // Instantiate Asr Calculation
        switch($asr_method)
        {
            case "standard":
                $prayer_times->setMathhab(Prayer::MATHHAB_STANDARD);
                break;
            case "hanafi":
                $prayer_times->setMathhab(Prayer::MATHHAB_HANAFI);
                break;
        }

        // Instantiate High Latitude resolves
        switch($high_lat)
        {
            case "middle_of_night":
                $prayer_times->setHighLatitudeRule(Prayer::HIGH_LATITUDE_MIDDLE_OF_NIGHT);
                break;
            case "seventh_of_night":
                $prayer_times->setHighLatitudeRule(Prayer::HIGH_LATITUDE_SEVENTH_OF_NIGHT);
                break;
            case "twilight_angle":
                $prayer_times->setHighLatitudeRule(Prayer::HIGH_LATITUDE_TWILIGHT_ANGLE);
                break;
        }

        // Perform adjustments
        if(isset($adjustments))
        {
            $prayer_times->setAdjustments(
                self::get_if_exists($adjustments, "fajr"),
                self::get_if_exists($adjustments, "sunrise"),
                self::get_if_exists($adjustments, "duhr"),
                self::get_if_exists($adjustments, "asr"),
                self::get_if_exists($adjustments, "maghrib"),
                self::get_if_exists($adjustments, "isha")
            );
        }

        // Get prayer times
        $times = $prayer_times->times($date);

        // Set timezones
        $times->setTimeZone($timezone);

        /**
         * Response format:
         * {
         *  'success': <true if success>
         *  'data':
         *      {
         *          "fajr": <time>,
         *          "sunrise": <time>,
         *          "duhr": <time>,
         *          "asr": <time>,
         *          "maghrib": <time>,
         *          "isha": <time>,
         *          "current": <time>,
         *          "next": <time>,
         *      }
         * }
         */
        // build response
        $response = [
            "success" => True,
            "data" => [
                Times::TIME_FAJR => $times->fajr->format($time_format),
                Times::TIME_SUNRISE => $times->sunrise->format($time_format),
                Times::TIME_DUHR => $times->duhr->format($time_format),
                Times::TIME_ASR => $times->asr->format($time_format),
                Times::TIME_MAGHRIB => $times->maghrib->format($time_format),
                Times::TIME_ISHA => $times->isha->format($time_format),
            ]
        ];
        if($times->currentPrayer() !== NULL)
        {
            $response["data"]["current"] = $times->currentPrayer();
        }
        if($times->nextPrayer() !== NULL)
        {
            $response["data"]["next"] = $times->nextPrayer();
        }
        return response()->json($response);
    }
}
