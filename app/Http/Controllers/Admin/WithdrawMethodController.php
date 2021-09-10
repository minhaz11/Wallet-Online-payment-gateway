<?php

namespace App\Http\Controllers\Admin;

use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\WithdrawMethod;
use App\Rules\FileTypeValidate;
use App\Http\Controllers\Controller;

class WithdrawMethodController extends Controller
{
    public function methods()
    {
        $pageTitle = 'Withdrawal Methods';
        $emptyMessage = 'Withdrawal Methods not found.';
        $methods = WithdrawMethod::orderBy('status','desc')->orderBy('id')->get();
        return view('admin.withdraw.index', compact('pageTitle', 'emptyMessage', 'methods'));
    }

    public function create()
    {
        $pageTitle = 'New Withdrawal Method';
        $currencies = Currency::where('status',1)->get();
        return view('admin.withdraw.create', compact('pageTitle','currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max: 60',
            'currencies*' => 'required|integer',
            'user_guards' => 'required',
            'user_guards.*' => 'required|in:1,2,3',
            'image' => [
                'required',
                'image',
                new FileTypeValidate(['jpeg', 'jpg', 'png'])],
            'min_limit' => 'required|numeric|gt:0',
            'max_limit' => 'required|numeric|gt:min_limit',
            'fixed_charge' => 'required|numeric|gte:0',
            'percent_charge' => 'required|numeric|between:0,100',
            'instruction' => 'required|max:64000',
            'field_name.*'    => 'sometimes|required'
        ],[
            'field_name.*.required'=>'All field is required'
        ]);
        $filename = '';
        $path = imagePath()['withdraw']['method']['path'];
        $size = imagePath()['withdraw']['method']['size'];
        if ($request->hasFile('image')) {
            try {
                $filename = uploadImage($request->image, $path, $size);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Image could not be uploaded.'];
                return back()->withNotify($notify);
            }
        }

        $input_form = [];
        if ($request->has('field_name')) {
            for ($a = 0; $a < count($request->field_name); $a++) {
                $arr = [];
                $arr['field_name'] = strtolower(str_replace(' ', '_', sanitizedParam($request->field_name[$a])));
                $arr['field_level'] = $request->field_name[$a];
                $arr['type'] = $request->type[$a];
                $arr['validation'] = $request->validation[$a];
                $input_form[$arr['field_name']] = $arr;
            }
        }

        $method = new WithdrawMethod();
        $method->name = $request->name;
        $method->image = $filename;
        $method->currencies = $request->currencies;
        $method->user_guards  = $request->user_guards;
        $method->min_limit = $request->min_limit;
        $method->max_limit = $request->max_limit;
        $method->fixed_charge = $request->fixed_charge;
        $method->percent_charge = $request->percent_charge;
        $method->description = $request->instruction;
        $method->user_data = $input_form;
        $method->save();
        $notify[] = ['success', $method->name . ' has been added.'];
        return redirect()->route('admin.withdraw.method.index')->withNotify($notify);
    }


    public function edit($id)
    {
        $pageTitle = 'Update Withdrawal Method';
        $method = WithdrawMethod::findOrFail($id);
        $currencies = Currency::where('status',1)->get();
        return view('admin.withdraw.edit', compact('pageTitle', 'method','currencies'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'           => 'required|max: 60',
            'image'          => ['image', new FileTypeValidate(['jpeg', 'jpg', 'png'])],
            'min_limit'      => 'required|numeric|gt:0',
            'max_limit'      => 'required|numeric|gt:min_limit',
            'currencies*'    => 'required|integer',
            'user_guards'    => 'required',
            'user_guards.*'  => 'required|in:1,2,3',
            'fixed_charge'   => 'required|numeric|gte:0',
            'percent_charge' => 'required|numeric|between:0,100',
            'instruction'    => 'required|max:64000',
            'field_name.*'   => 'sometimes|required'
        ],[
            'field_name.*.required'=>'All field is required'
        ]);

        $method = WithdrawMethod::findOrFail($id);
        $filename = $method->image;

        $path = imagePath()['withdraw']['method']['path'];
        $size = imagePath()['withdraw']['method']['size'];

        if ($request->hasFile('image')) {
            try {
                $filename = uploadImage($request->image,$path, $size, $method->image);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Image could not be uploaded.'];
                return back()->withNotify($notify);
            }
        }


        $input_form = [];
        if ($request->has('field_name')) {
            for ($a = 0; $a < count($request->field_name); $a++) {
                $arr = [];
                $arr['field_name'] = strtolower(str_replace(' ', '_', sanitizedParam($request->field_name[$a])));
                $arr['field_level'] = $request->field_name[$a];
                $arr['type'] = $request->type[$a];
                $arr['validation'] = $request->validation[$a];
                $input_form[$arr['field_name']] = $arr;
            }
        }

        $method->name = $request->name;
        $method->image = $filename;
        $method->currencies = $request->currencies;
        $method->user_guards  = $request->user_guards;
        $method->min_limit = $request->min_limit;
        $method->max_limit = $request->max_limit;
        $method->fixed_charge = $request->fixed_charge;
        $method->percent_charge = $request->percent_charge;
        $method->description = $request->instruction;
        $method->user_data = $input_form;
        $method->save();

        $notify[] = ['success', $method->name . ' has been updated.'];
        return back()->withNotify($notify);
    }



    public function activate(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $method = WithdrawMethod::findOrFail($request->id);
        $method->status = 1;
        $method->save();
        $notify[] = ['success', $method->name . ' has been activated.'];
        return redirect()->route('admin.withdraw.method.index')->withNotify($notify);
    }

    public function deactivate(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $method = WithdrawMethod::findOrFail($request->id);
        $method->status = 0;
        $method->save();
        $notify[] = ['success', $method->name . ' has been deactivated.'];
        return redirect()->route('admin.withdraw.method.index')->withNotify($notify);
    }

}
