<?php namespace App\Repositories\Api\Invoice;


use Illuminate\Http\Request;
use App\DB\Api\Invoice;
use DB;

class EloquentInvoiceRepository implements InvoiceRepository
{
    protected $merchant_id;

    function __construct(Request $request)
    {
        $this->merchant_id = getMerchantId($request->header('Authorization'));
        date_default_timezone_set('Asia/Dhaka');
    }

    public function getInvoiceList($per_page = 20) {
        $rows =  DB::table('deliveries as d')
            ->select(
                'i.invoice_date', 'i.id as invoice_id',
                DB::raw('COALESCE(SUM(CASE WHEN d.payment_status = 1 THEN d.receive_amount ELSE 0 END), 0) AS collected'),
                DB::raw('COALESCE(SUM(d.cod_charge), 0) as cod'),
                DB::raw('COALESCE((SUM(d.charge) + SUM(d.cod_charge)), 0) as fees'),
                DB::raw('COALESCE(SUM(CASE WHEN status = 6 THEN 1 ELSE 0 END), 0) AS delivered'),
                DB::raw('COALESCE(SUM(CASE WHEN status IN(7,8) THEN 1 ELSE 0 END), 0) AS returned')
            )
            ->join('invoice_details as idls','idls.delivery_id', '=', 'd.id')
            ->join('invoices as i','i.id', '=', 'idls.invoice_id')
            ->where([ 'd.merchant_id' => $this->merchant_id, 'd.payment_status' => 1 ])
            ->orderBy('d.invoice_date', 'desc')
            ->groupBy(DB::raw('i.id'))
            ->paginate($per_page);

        return $rows;
    }

    public function findInvoice($invoice_id) {
        $rows =  DB::table('deliveries as d')
            ->select(
                'd.*',
                'd.cod_charge as cod',
                'fs.flag_text as status_text',
                'fs.color_code as status_color',
                DB::raw("d.charge + d.cod_charge as total_charge"),
                's.name as store_name',
                'p.plan_name',
                'cz.zone_name as recipient_zone_name'
            )
            ->join('flag_status as fs', 'fs.id', '=', 'd.status')
            ->leftJoin('stores as s', 's.id', '=', 'd.store_id')
            ->leftJoin('plans as p', 'p.id', '=', 'd.plan_id')
            ->leftJoin('courier_zones as cz', 'cz.id', '=', 'd.recipient_zone_id')
            ->join('invoice_details as idls','idls.delivery_id', '=', 'd.id')
            ->join('invoices as i','i.id', '=', 'idls.invoice_id')
            ->where([ 'i.id' => $invoice_id, 'd.merchant_id' => $this->merchant_id, 'd.payment_status' => 1 ])
            ->orderBy('d.invoice_date', 'desc')
            ->get();
        return $rows;
    }
}
