<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $invoice_number
 * @property InvoiceStatus $invoice_status
 * @property string|null $invoice_path
 */
class Order extends Model
{
    protected function casts()
    {
        return [
            'invoice_status' => InvoiceStatus::class,
        ];
    }
}
