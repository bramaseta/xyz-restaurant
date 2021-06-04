<?php

namespace App\Http\Controllers\API;

use Midtrans\Config;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        // Set Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // Buat Instance midtrans notification 
        $notification = new Notification();

        // Asign Ke Variabel Untuk Memudahkan Koding
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        // Cari Transaksi BErdasarkan ID
        $transaction = Transaction::findOrFail($order_id);

        // Handle Nitifikasi Status Midtrans
        if($status == 'capture')
        {
            if($type == 'credit_card')
            {
                if($fraud == 'challenge')
                {
                    $transaction->status = "PENDING";
                }
                else
                {
                    $transaction->status = "SUCCESS";
                }

            }
        }
        else if($status == 'settlement')
        {
            $transaction->status = "SUCCESS";
        }

        else if($status == 'pending')
        {
            $transaction->status = "PENDING";
        }

        else if($status == 'deny')
        {
            $transaction->status = "CANCELLED";
        }

        else if($status == 'expire')
        {
            $transaction->status = "CANCELLED";
        }

        else if($status == 'cancel')
        {
            $transaction->status = "CANCELLED";
        }

        // Proses Simpan Transaksi
        $transaction->save();    
    }

    public function success()
    {
        return view('midtrans.success');
    }

    public function unfinish()
    {
        return view('midtrans.unfinish');
    }

    public function error()
    {
        return view('midtrans.error');
    }
}
