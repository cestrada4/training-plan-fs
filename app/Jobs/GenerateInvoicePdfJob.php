<?php

/**
 * Day 4 — Defensive Coding: Fix It Fresh
 *
 * This is a NEW file — not the Day 1 baseline file. Same bug family as Day 1, different scenario.
 * You have not seen this code before.
 *
 * TASK: This is a Laravel queued job that renders an order's invoice as HTML and shells out to a
 * PDF renderer to produce the customer-facing invoice PDF. Find and fix the defensive-coding issues,
 * ship working code (not a written list) via a PR against this file.
 */


namespace App\Jobs;

use App\Models\Order;
use ErrorException;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Queueable;
    protected string $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::find($this->orderId);
        $order->invoice_status = 'rendering';
        $order->save();

        $htmlPath = storage_path('app/tmp/invoice-' . $this->orderId . '.html');
        file_put_contents($htmlPath, view('invoices.customer', ['order' => $order])->render());

        $pdfPath = storage_path('app/invoices/' . $order->invoice_number . '.pdf');
        exec("wkhtmltopdf {$htmlPath} {$pdfPath}");

        $order->invoice_status = 'ready';
        $order->invoice_path = $pdfPath;
        $order->save();
    }
}
