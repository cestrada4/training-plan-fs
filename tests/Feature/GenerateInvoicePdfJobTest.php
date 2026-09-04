<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Tests\TestCase;

class GenerateInvoicePdfJobTest extends TestCase
{
    use RefreshDatabase;

    private function createOrder(string $invoice_number)
    {
        $order = new Order();
        $order->invoice_number = $invoice_number;
        $order->invoice_status = InvoiceStatus::Pending;
        $order->save();
        return $order;
    }
    public function test_should_fail_if_order_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $job = new GenerateInvoicePdfJob("random");
        $job->handle();
    }

    public function test_should_generate_invoice_pdf(): void
    {
        $order = $this->createOrder("TEST-001");

        $job = new GenerateInvoicePdfJob((string) $order->getKey());
        $job->handle();
        $order->refresh();

        $htmlPath = storage_path("app/tmp/invoice-{$order->getKey()}.html");

        try {
            $this->assertFileExists($order->invoice_path);
            $this->assertFileDoesNotExist($htmlPath);
        } finally {
            @unlink($order->invoice_path);
            @unlink($htmlPath);
        }
    }

    public function test_should_throw_exception_if_wkhtmltopdf_failed(): void
    {
        Process::fake(fn() => Process::result(
            errorOutput: 'wkhtmltopdf failed',
            exitCode: 1,
        ));

        $order = $this->createOrder("test");

        $job = new GenerateInvoicePdfJob((string) $order->getKey());

        $this->expectException(RuntimeException::class);
        $job->handle();
        
        $this->assertSame(InvoiceStatus::Pending, $order->refresh()->invoice_status);
    }

    public function test_sets_status_to_failed_after_job_failure(): void
    {
        $order = $this->createOrder("test");
        $job = new GenerateInvoicePdfJob((string) $order->getKey());
        $job->failed(new RuntimeException('Something failed'));

        $this->assertSame(
            InvoiceStatus::Failed,
            $order->fresh()->invoice_status,
        );
    }

}
