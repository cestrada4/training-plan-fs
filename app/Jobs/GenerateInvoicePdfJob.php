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

use App\Enums\InvoiceStatus;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use Log;
use RuntimeException;
use Throwable;
use function PHPUnit\Framework\fileExists;

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
        $order = Order::findOrFail($this->orderId); // Handle if order can't be found ,exit with a fail
        $order->invoice_status = InvoiceStatus::Rendering;
        $order->save();

        $htmlPath = storage_path('app/tmp/invoice-' . $this->orderId . '.html');
        $result = file_put_contents($htmlPath, view('invoices.customer', ['order' => $order])->render());
        if ($result === false) { // failed to write
            $order->invoice_status = InvoiceStatus::Pending;
            $order->save();
            throw new RuntimeException('Failed to write invoice.');
        }

        $pdfPath = storage_path('app/invoices/' . $order->invoice_number . '.pdf');
        $process = Process::run(['wkhtmltopdf', $htmlPath, $pdfPath]); // safer than exec(), provided by laravel
        @unlink($htmlPath);
        if ($process->failed()) {
            $order->invoice_status = InvoiceStatus::Pending;
            $order->save();
            throw new RuntimeException("Failed to convert html to pdf: {$process->errorOutput()}");
        }

        if(!@file_exists($pdfPath)) {
            throw new RuntimeException("PDF was not created.");
        }
        
        $order->invoice_status = InvoiceStatus::Ready;
        $order->invoice_path = $pdfPath;
        $order->save();
    }

    public function failed(?Throwable $exception): void //
    {
        $order = Order::find($this->orderId);
        if (!$order) {
            Log::error("Job failed due to the order not being found with id: {$this->orderId}");
            return;
        }

        Log::error("Job failed for {$this->orderId}: {$exception->getMessage()}");
        $order->invoice_status = InvoiceStatus::Failed;
        $order->save();
    }
}
