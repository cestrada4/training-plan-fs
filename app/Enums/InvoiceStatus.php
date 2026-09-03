<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Pending = 'pending';
    case Rendering = 'rendering';
    case Ready = 'ready';
    case Failed = 'failed';
}
