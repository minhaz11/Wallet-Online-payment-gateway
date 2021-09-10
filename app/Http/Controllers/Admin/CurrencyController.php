<?php

namespace App\Http\Controllers\Admin;

use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Http\Controllers\Controller;

class CurrencyController extends Controller
{
    public function currencies(Request $request)
    {
        $search = $request->search;
        if($search){
            $pageTitle = "Search Results of $search";
            $currencies = Currency::where('currency_code','like',"%$search%")->orWhere('currency_fullname','like',"%$search%")->latest()->paginate(getPaginate());
        } else {
            $pageTitle = "Manage Currencies";
            $currencies = Currency::orderBy('is_default','desc')->paginate(getPaginate());
        }
        $emptyMessage = "No currencies available";
        return view('admin.currency.all_currencies',compact('pageTitle','emptyMessage','currencies','search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'currency_fullname' => 'required',
            'currency_code' => 'required|unique:currencies',
            'currency_symbol' => 'required|unique:currencies',
            'currency_type' => 'required|in:1,2',
        ]);
        $general = GeneralSetting::first();
        $currency = new Currency();
        $currency->currency_fullname = $request->currency_fullname;
        $currency->currency_code = strtoupper($request->currency_code);
        $currency->currency_symbol = $request->currency_symbol;
        $currency->rate = $request->rate;
        $currency->currency_type = $request->currency_type;
        $currency->status = $request->status ? 1 : 0;

        if ($request->is_default) {
            $default = $currency->where('is_default', 1)->first();
            if ($default) {
                $default->is_default = 0;
                $default->save();
            }
            $currency->status = 1;
            $general->cur_text = strtoupper($request->currency_code);
            $general->cur_sym = $request->currency_symbol;
            $general->save();
        }
        $currency->is_default = $request->is_default ? 1 : 0;
        $currency->save();
        $notify[]=['success','Currency Added successfully'];
        return back()->withNotify($notify);

    }
    public function update(Request $request)
    {
        $currency = Currency::findOrFail($request->currency_id);
      
        $request->validate([
            'currency_fullname' => 'required',
            'currency_code' => 'required|unique:currencies,currency_code,'.$currency->id,
            'currency_symbol' => 'required|unique:currencies,currency_symbol,'.$currency->id,
            'currency_type' => 'required|in:1,2',
        ]);
        $general = GeneralSetting::first();
        $currency->currency_fullname = $request->currency_fullname;
        $currency->currency_code = strtoupper($request->currency_code);
        $currency->currency_symbol = $request->currency_symbol;
        $currency->rate = $request->rate;
        $currency->currency_type = $request->currency_type;
        $currency->status = $request->status ? 1 : 0;
        if ($request->is_default) {
            $default = $currency->where('is_default', 1)->first();
            if ($default) {
                $default->is_default = 0;
                $default->save();
            }
            $currency->status = 1;
            $general->cur_text = strtoupper($request->currency_code);
            $general->cur_sym = $request->currency_symbol;
            $general->save();
        }

        $currency->is_default = $request->is_default ? 1 : 0;
        $currency->save();
        $notify[]=['success','Currency Added successfully'];
        return back()->withNotify($notify);

    }

    public function updateApiKey(Request $request)
    {
        $request->validate([
            'fiat_api_key' => 'required',
            'crypto_api_key' => 'required',
        ]);
        $gnl = GeneralSetting::first();
        $gnl->fiat_currency_api = $request->fiat_api_key;
        $gnl->crypto_currency_api = $request->crypto_api_key;
        $gnl->save();
        $notify[]=['success','Api Key Updated Successfully'];
        return back()->withNotify($notify);

    }
    
    
    
}
