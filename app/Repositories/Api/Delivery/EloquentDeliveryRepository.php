<?php namespace App\Repositories\Api\Delivery;

use Illuminate\Http\Request;
use App\DB\Api\Delivery;
use DB;

class EloquentDeliveryRepository implements DeliveryRepository
{
    protected $merchant_id;

    function __construct(Request $request)
    {
        $this->merchant_id = getMerchantId($request->header('Authorization'));
        date_default_timezone_set('Asia/Dhaka');
    }

    public function getDeliveryList($request, $per_page = 20) {
        $rows =  DB::table('deliveries as d')
            ->select(
                'd.*',
                'fs.flag_text as status_text', 'fs.color_code as status_color',
                's.name as store_name',
                'p.plan_name',
                'cz.zone_name as recipient_zone_name'
            )
            ->join('flag_status as fs', 'fs.id', '=', 'd.status')
            ->leftJoin('stores as s', 's.id', '=', 'd.store_id')
            ->leftJoin('plans as p', 'p.id', '=', 'd.plan_id')
            ->leftJoin('courier_zones as cz', 'cz.id', '=', 'd.recipient_zone_id')
            ->where([ 'd.merchant_id' => $this->merchant_id ]);
        if ($request->get('type') == 'aging') {
            $rows = $rows->whereIn('d.status', [1,9]);
        }
        if (isset($request->query_string) && !empty($request->query_string))
        {
            $input = $request->query_string;
            //echo $input;exit();
            $rows = $rows->where(function($q) use($input) {
                $q->where('consignment_id', 'LIKE', '%' . $input . '%');
                $q->orWhere('recipient_number', 'LIKE', '%' . $input . '%');
                $q->orWhere('merchant_order_id', 'LIKE', '%' . $input . '%');
                $q->orWhere('recipient_name', 'LIKE', '%' . $input . '%');
            });

        }

        $rows = $rows->orderBy('d.id', 'desc')
            ->paginate($per_page);

        if(!empty($rows)) {
            foreach ($rows as $key => $row) {
                $row->payment_status = $row->payment_status === 0 ? 'Unpaid' : 'Paid';
                $row->products = $this->getProductsByDelivery($row->id);
            }
        }

        return $rows;
    }

    private function getProductsByDelivery($delivery_id) {
        return DB::table('products')
        ->select('id', 'name')
        ->whereRaw("id IN (SELECT product_id FROM delivery_product WHERE delivery_id = {$delivery_id})")
        ->get();
    }

    public function findDelivery($id) {
        $row =  DB::table('deliveries as d')
            ->select(
                'd.*',
                's.name as store_name',
                'p.plan_name',
                'cz.zone_name as recipient_zone_name'
            )
            ->leftJoin('stores as s', 's.id', '=', 'd.store_id')
            ->leftJoin('plans as p', 'p.id', '=', 'd.plan_id')
            ->leftJoin('courier_zones as cz', 'cz.id', '=', 'd.recipient_zone_id')
            ->where([ 'd.id' => $id, 'd.merchant_id' => $this->merchant_id ])
            ->orderBy('d.id', 'desc')
            ->first();

        if(!empty($row)) {
            $row->products = $this->getProductsByDelivery($row->id);
        }
        return $row;
    }

    public function getDeliveryTrackingLogs($id){
        return DB::table('tracking_details as td')
            ->select(
                'td.*'
            )
            ->where([ 'td.deliveries_id' => $id ])
            ->orderBy('td.id', 'asc')
            ->get();
    }

    public function postStoreDelivery($input, $id = null)
    {
        $hub_id = $this->getHubIdByMerchantId();
        if($id > 0) {
            $delivery = Delivery::find($id);
            if ($delivery->status > 1)
            {
                return 'i';
            }
        } else {
            $delivery = new Delivery();
            $delivery->consignment_id        = strtoupper(getUniqueConsignmentId(8));
        }
        $delivery->merchant_id           = $this->merchant_id;
        $delivery->recipient_name        = $input['recipient_name'];
        $delivery->recipient_number      = $input['recipient_number'];
        $delivery->recipient_email       = isset($input['recipient_email']) ? $input['recipient_email'] : '';
        $delivery->recipient_zone_id     = isset($input['recipient_zone_id']) ? $input['recipient_zone_id'] : 0;
        $delivery->recipient_address     = $input['recipient_address'];
        $delivery->latitude              = $input['latitude'];
        $delivery->longitude             = $input['longitude'];
        $delivery->package_description   = isset($input['package_description']) ? $input['package_description'] : '';
        $delivery->amount_to_be_collected = isset($input['amount_to_be_collected']) ? $input['amount_to_be_collected'] : 0;
        $delivery->charge                = $this->getCharge($input['plan_id']);
        $delivery->store_id              = isset($input['store_id']) ? $input['store_id'] : 0;
        $delivery->plan_id               = isset($input['plan_id']) ? $input['plan_id'] : 0;
        $delivery->delivery_note         = isset($input['delivery_note']) ? $input['delivery_note'] : '';
        $delivery->special_instruction   = isset($input['special_instruction']) ? $input['special_instruction'] : '';
        $delivery->merchant_order_id     = isset($input['merchant_order_id']) ? $input['merchant_order_id'] : '';
        $delivery->delivery_date         = isset($input['delivery_date']) ? $input['delivery_date'] : '';
        $delivery->hub_id                = $hub_id;
        $delivery->status                = 1;
        $delivery->created_at            = date('Y-m-d H:i:s');
        if ($delivery->save()) {
            if (isset($input['products'])) {
                $this->deliveryProduct($input['products'], $delivery->id, $id);
            }
            // Save to details table
            storeTrackingData([
                'deliveries_id'     => $delivery->id,
                'flag_status_id'    => 1,
                'assign_to'         => 0,
                'is_hub'            => 0,
                'notes'             => '',
                'description'       => '',
                'in_out'            => 1,
                'hub_id'            => $hub_id
            ]);
            return $delivery->id;
        }
        return 0;
    }

    private function getCharge($plan_id) {
        $plan = DB::table('plans')
            ->select('charge')
            ->where([ 'id' => $plan_id ])
            ->first();
        if (!empty($plan)) {
            return $plan->charge;
        }
        return 0;
    }

    private function getHubIdByMerchantId() {
        $data = DB::table('merchants')
            ->select('hub_id')
            ->where([ 'id' => $this->merchant_id ])
            ->first();
        if (!empty($data)) {
            return $data->hub_id;
        }
        return 0;
    }

    private function deliveryProduct($products, $delivery_id, $id) {
        if($id > 0) {
           // Delete existing
            DB::table('delivery_product')->where('delivery_id', $delivery_id)->delete();
        }
        $products = explode(',', $products);
        if (!empty($products)) {
            foreach ($products as $k => $cat_id) {
                DB::table('delivery_product')->insert(
                    [
                        'delivery_id'    => $delivery_id,
                        'product_id'     => $cat_id,
                        'created_at'     => date('Y-m-d H:i:s'),
                        'updated_at'     => date('Y-m-d H:i:s')
                    ]
                );
            }
        }
        return true;
    }
}
