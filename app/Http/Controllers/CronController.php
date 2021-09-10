<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\GeneralSetting;

class CronController extends Controller
{

    public function fiatRate()
    {

        $general      = GeneralSetting::first();
        $endpoint     = 'live';
        $access_key   = $general->fiat_currency_api;
        $baseCurrency = defaultCurrency();
        $ch           = curl_init('http://apilayer.net/api/' . $endpoint . '?access_key=' . $access_key . '&source=' . $baseCurrency);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        $exchangeRates = json_decode($json);

        if ($exchangeRates->success == false) {
            $errorMsg = $exchangeRates->error->info;
            echo "$errorMsg";
        } else {
            foreach ($exchangeRates->quotes as $key => $rate) {
                $curcode  = substr($key, -3);
               
                $currency = Currency::where('currency_code', $curcode)->first();
                if ($currency) {
                    $currency->rate = 1/$rate;
                   $currency->update();
                }
            }
            echo "EXECUTED";
        }
    }

    public function cryptoRate()
    {
        $general = GeneralSetting::first();
        $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
        $cryptos = Currency::where('currency_type', 2)->pluck('currency_code')->toArray();
        $cryptos = implode(',',$cryptos);

        $parameters = [
            'symbol' => $cryptos,
            'convert' => defaultCurrency(),
        ];
        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY:' . trim($general->crypto_currency_api),
        ];
        $qs      = http_build_query($parameters); // query string encode the parameters
        $request = "{$url}?{$qs}"; // create the request URL
        $curl    = curl_init(); // Get cURL resource
        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $request, // set the request URL
            CURLOPT_HTTPHEADER     => $headers, // set the headers
            CURLOPT_RETURNTRANSFER => 1, // ask for raw response instead of bool
        ));
        $response = curl_exec($curl); // Send the request, save the response
        curl_close($curl); // Close request

        $a = json_decode($response);

        if (!$a->data) {
            return 'error';
        }
        $coins = $a->data;
        foreach ($coins as $coin) {
            $currency = Currency::where('currency_code', $coin->symbol)->first();
            if ($currency) {
            $defaultCurrency = defaultCurrency();
                $currency->rate = $coin->quote->$defaultCurrency->price;
                $currency->save();

            }
        }
       echo "EXECUTED";
    }

}
