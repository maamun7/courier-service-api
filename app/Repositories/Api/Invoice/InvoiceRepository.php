<?php namespace App\Repositories\Api\Invoice;

interface InvoiceRepository
{
    public function getInvoiceList($per_page = 20);
    public function findInvoice($data);
}
