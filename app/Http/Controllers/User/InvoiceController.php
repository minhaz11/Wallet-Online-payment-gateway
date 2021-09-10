<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Currency;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use App\Models\TransactionCharge;

class InvoiceController extends Controller
{
    public function __construct() {
        $this->activeTemplate = activeTemplate();
    }
    public function invoices(Request $request)
    {
        $search = $request->search;
        if($search){
            $pageTitle = "Search result of $search";
            $invoices = Invoice::where('user_id',auth()->id())->where('invoice_num',$search)->with(['items','currency'])->whereHas('currency')->latest()->paginate(getPaginate());
        } else {
            $pageTitle = "Invoices";
            $invoices = Invoice::where('user_id',auth()->id())->orderBy('pay_status','DESC')->with(['items','currency'])->whereHas('currency')->latest()->paginate(getPaginate());
        }
        return view($this->activeTemplate.'user.invoice.list',compact('pageTitle','invoices','search'));
    }
    public function createInvoice()
    {
        $permission = module('create_invoice');
        if($permission->status == 0){
            $notify[]=['error','Creating invoice is currently not available'];
            return back()->withNotify($notify);
        }
        $pageTitle = "Create Invoice";
        $invoiceCharge = TransactionCharge::where('slug','invoice_charge')->first();
        $currencies = Currency::get();
        return view($this->activeTemplate.'user.invoice.create',compact('pageTitle','invoiceCharge','currencies'));

        
    }

    public function createInvoiceConfirm(Request $request)
    {
        $request->validate(
        [
            'invoice_to' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'item_name' => 'required',
            'item_name.*' => 'required',
            'amount' => 'required',
            'amount.*' => 'required|numeric|gt:0',
            'currency_id' => 'required|integer'
        ],
        [
            'item_name.*.required' => 'Item name fields required',
            'amount.*.required' => 'Amount fields required',
            'amount.*.gt' => 'Amount fields must be greater than 0',
            'amount.*.numeric' => 'Amount fields value should be numeric',
        ]   
      );
        $permission = module('create_invoice');
        if($permission->status == 0){
            $notify[]=['error','Creating invoice is currently not available'];
            return back()->withNotify($notify);
        }

        $invoiceCharge = TransactionCharge::where('slug','invoice_charge')->first();
        $currency = Currency::find($request->currency_id);
        if(!$currency){
            $notify[]=['error','Sorry! Currency not found'];
            return back()->withNotify($notify);
        }

        $rate = $currency->rate;
        $initialAmount = array_sum($request->amount);
        if($currency->currency_type == 1){
            $cap = getAmount($invoiceCharge->cap/$rate,2);
            $fixedCharge = getAmount($invoiceCharge->fixed_charge/$rate,2);
        } else {
            $cap = getAmount($invoiceCharge->cap/$rate,8);
            $fixedCharge = getAmount($invoiceCharge->fixed_charge/$rate,8);
        }
        $percentCharge = ($initialAmount*$invoiceCharge->percent_charge)/100;
       
        $totalCharge = $fixedCharge +  $percentCharge;
         
        if($invoiceCharge->cap != -1 && $totalCharge > $cap){
            $totalCharge = $cap;
        }
        
        $getAmount = $initialAmount - $totalCharge;

        $invoice = new Invoice();
        $invoice->user_id = auth()->id();
        $invoice->user_type = 'USER';
        $invoice->invoice_num = getTrx(12);
        $invoice->invoice_to = $request->invoice_to;
        $invoice->email = $request->email;
        $invoice->address = $request->address;
        $invoice->currency_id = $request->currency_id;
        $invoice->charge = $totalCharge;
        $invoice->total_amount = $initialAmount;
        $invoice->get_amount = $getAmount;
        $invoice->pay_status = 0;
        $invoice->status = 0;
        $invoice->save();

        $items = array_combine($request->item_name,$request->amount);
        foreach($items as $itemName => $itemAmount){
            $invoiceItem = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->item_name	 = $itemName;
            $invoiceItem->amount	 = $itemAmount;
            $invoiceItem->save();
        }

       $notify[]=['success','Invoice Created Successfully'];
       return redirect(route('user.invoices'))->withNotify($notify);

    }

    public function editInvoice($invoiceNum)
    {
        $pageTitle = "Update Invoice";
        $invoice = Invoice::where('invoice_num',$invoiceNum)->where('user_id',auth()->id())->first();
        if(!$invoice){
            $notify[]=['error','Sorry! invoice not found'];
            return back()->withNotify($notify);
        }

        $invoiceItems = InvoiceItem::where('invoice_id', $invoice->id)->get();
        $invoiceCharge = TransactionCharge::where('slug','invoice_charge')->first();
        $currencies = Currency::get();
        return view($this->activeTemplate.'user.invoice.update',compact('pageTitle','invoice','invoiceItems','invoiceCharge','currencies'));

    }


    public function updateInvoice(Request $request)
    {

        $request->validate(
        [
            'invoice_to' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'item_name' => 'required',
            'item_name.*' => 'required',
            'amount' => 'required',
            'amount.*' => 'required|numeric|gt:0',
            'currency_id' => 'required|integer'
        ],
        [
            'item_name.*.required' => 'Item name fields required',
            'amount.*.required' => 'Amount fields required',
            'amount.*.gt' => 'Amount fields must be greater than 0',
            'amount.*.numeric' => 'Amount fields value should be numeric',
        ]   
       );
        

        $invoiceCharge = TransactionCharge::where('slug','invoice_charge')->first();
        $currency = Currency::find($request->currency_id);
        if(!$currency){
            $notify[]=['error','Sorry! Currency not found'];
            return back()->withNotify($notify);
        }

        $rate = $currency->rate;
        $initialAmount = array_sum($request->amount);
        $fixedCharge = $invoiceCharge->fixed_charge/$rate;
        $percentCharge = ($initialAmount*$invoiceCharge->percent_charge)/100;
       
        if($currency->currency_type == 1){
            $cap = getAmount($invoiceCharge->cap/$rate,2);
            $totalCharge = getAmount($fixedCharge +  $percentCharge,2);
        } else {
            $cap = getAmount($invoiceCharge->cap/$rate,8);
            $totalCharge = getAmount($fixedCharge +  $percentCharge,8);
        }
        
        if($totalCharge > $cap){
            $totalCharge = $cap;
        }
        $getAmount = $initialAmount - $totalCharge;

        $invoice = Invoice::findOrFail($request->invoice_id);
        $invoice->user_id = auth()->id();
        $invoice->user_type = 'USER';
        $invoice->invoice_to = $request->invoice_to;
        $invoice->email = $request->email;
        $invoice->address = $request->address;
        $invoice->currency_id = $request->currency_id;
        $invoice->charge = $totalCharge;
        $invoice->total_amount = $initialAmount;
        $invoice->get_amount = $getAmount;
        $invoice->save();

        InvoiceItem::where('invoice_id',$invoice->id)->delete();
        $items = array_combine($request->item_name,$request->amount);
      
        foreach($items as $itemName => $itemAmount){
            $invoiceItem = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->item_name	 = $itemName;
            $invoiceItem->amount	 = $itemAmount;
            $invoiceItem->save();
        }

       $notify[]=['success','Invoice Update Successfully'];
       return redirect(route('user.invoice.edit',$invoice->invoice_num))->withNotify($notify);
    }


    public function sendInvoiceToMail($invoiceNum)
    {

        $invNum = decrypt($invoiceNum);
        $invoice = Invoice::where('invoice_num',$invNum)->first();
        if(!$invoice){
            $notify[]=['error','Invoice not found'];
            return back()->withNotify($notify);
        }
        $userData = [
            'fullname' => $invoice->invoice_to,
            'username' => $invoice->invoice_to,
            'email' => $invoice->email,
        ];
        $user = json_decode(json_encode($userData));
       try{
            sendEmail($user, 'SEND_INVOICE_MAIL', [
                'url' => route('invoice.payment',encrypt($invoice->invoice_num)),
                'download_url' => route('invoice.download',encrypt($invoice->invoice_num))
            ],false);
       }
       catch(\Exception $ex){
            $notify[]=['error','Sorry! mail can not send right now. Try again later'];
            return back()->withNotify($notify);
       }

        $notify[]=['success','Invoice sent to email successfully'];
        return redirect(route('user.invoice.edit',$invoice->invoice_num))->withNotify($notify);
    }

    public function downloadInvoice($invoiceNum)
    {
        $invNum = decrypt($invoiceNum);
        $invoice = Invoice::where('invoice_num',$invNum)->first();
        if(!$invoice){
            $notify[]=['error','Invoice not found'];
            return back()->withNotify($notify);
        }

        return 'ok';
    }
    public function publishInvoice($invoiceNum)
    {
        $invNum = decrypt($invoiceNum);
        $invoice = Invoice::where('invoice_num',$invNum)->first();
        if(!$invoice){
            $notify[]=['error','Invoice not found'];
            return back()->withNotify($notify);
        }

       $invoice->status = 1;
       $invoice->save();
       $notify[]=['success','Invoice published successfully'];
       return redirect(route('user.invoices'))->withNotify($notify);
    }
    public function discardInvoice($invoiceNum)
    {
        $invNum = decrypt($invoiceNum);
        $invoice = Invoice::where('invoice_num',$invNum)->first();
        if(!$invoice){
            $notify[]=['error','Invoice not found'];
            return back()->withNotify($notify);
        }

       $invoice->status = 2;
       $invoice->save();
       $notify[]=['success','Invoice has been discarded'];
       return redirect(route('user.invoices'))->withNotify($notify);
    }

    public function invoicePayment($invoiceNum)
    {
        try {
            $invNum = decrypt($invoiceNum);
        } catch (\Throwable $th) {
           $notify[]=['error','Invalid invoice number.'];
           return back()->withNotify($notify);
        }
        $pageTitle = "Invoice-#$invNum";
        $invoice = Invoice::where('invoice_num',$invNum)->first();
        if(!$invoice){
            $notify[]=['error','Invoice not found'];
            return redirect(route('home'))->withNotify($notify);
        }
        if($invoice->status == 0 ){
            $notify[]=['error','Sorry! invoice not published'];
            return redirect(route('home'))->withNotify($notify);
        }
        return view($this->activeTemplate.'invoice_page',compact('invoice','invoiceNum','pageTitle'));
    }

    public function invoicePaymentConfirm($invoiceNum)
    {
        $invNum = decrypt($invoiceNum);
        $invoice = Invoice::where('invoice_num',$invNum)->first();
        if(!$invoice){
            $notify[]=['error','Invoice not found'];
            return redirect(route('home'))->withNotify($notify);
        }
        session()->put('invoice',encrypt($invoice));
        session()->put('prev_url',route('invoice.payment',$invoiceNum));
        return redirect(route('user.payment.invoice'));

    }
    
}
