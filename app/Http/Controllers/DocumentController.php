<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Jenssegers\Agent\Agent; // Import Agent

class DocumentController extends Controller
{
    public function printReceipt($transactionId)
    {
        $transaction = Transaction::with(['transactionDetails.product', 'customer', 'user'])->findOrFail($transactionId);
        $agent = new Agent();

        if ($agent->isMobile()) {
            return view('documents.receipt-preview', compact('transaction'));
        }

        $pdf = Pdf::loadView('documents.receipt', compact('transaction'));

        return $pdf->stream('receipt_' . $transaction->invoice_number . '.pdf');
    }

    public function printInvoice($transactionId)
    {
        $transaction = Transaction::with(['transactionDetails.product', 'customer', 'user'])->findOrFail($transactionId);
        $agent = new Agent();

        if ($agent->isMobile()) {
            return view('documents.invoice-preview', compact('transaction'));
        }

        $pdf = Pdf::loadView('documents.invoice', compact('transaction'));

        return $pdf->stream('invoice_' . $transaction->invoice_number . '.pdf');
    }

    public function printExpiringStockReport(Request $request)
    {
        $expiry_threshold_months = $request->query('expiry_threshold_months', 2); // Default to 2 months
        $thresholdDate = Carbon::now()->addMonths((int)$expiry_threshold_months)->endOfDay();

        $productBatches = ProductBatch::with(['product', 'purchase.supplier'])
                                    ->where('stock', '>', 0)
                                    ->where('expiration_date', '<=', $thresholdDate)
                                    ->orderBy('expiration_date', 'asc')
                                    ->get();

        $pdf = Pdf::loadView('documents.expiring-stock-report', compact('productBatches', 'expiry_threshold_months'));

        return $pdf->setPaper('a4', 'portrait')->stream('laporan_stok_kedaluwarsa.pdf');
    }
}
