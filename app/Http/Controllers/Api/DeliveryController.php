<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

//Added
use App\Http\Controllers\Controller;
use App\Repositories\Api\Delivery\DeliveryRepository;
use Illuminate\Support\Facades\Input;
use Validator;
use DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class DeliveryController extends Controller
{
    protected $_errors;
    protected $_error_single_arr;
    protected $success_code;
    protected $error_code;
    protected $invalid_msg;
    protected $delivery;

    function __construct(DeliveryRepository $delivery) {
        $this->delivery = $delivery;
        $this->success_code = 200;
        $this->error_code = 200;
        $this->invalid_msg = 'Your request is not valid';
        date_default_timezone_set('Asia/Dhaka');
    }

    public function getDeliveryList(Request $request)
    {
        $deliveries = $this->delivery->getDeliveryList($request, $request->get('per_page') == '' ? 50 : $request->get('per_page'));

        if (empty($deliveries)) {
            $this->response['success']  = false;
            $this->response['code']     = '200';
            $this->response['message']  = "Didn't found any delivery !";
            $this->response['data']     = [];
            $this->response['error']    = get_error_response(404, "Didn't found any delivery!");
            return response($this->response, 200);
        }

        $deliveries = $deliveries->toArray();
        $deliveries['items'] = $deliveries['data'];
        unset($deliveries['data']);
        $this->response['success'] = true;
        $this->response['code'] = $this->success_code;
        $this->response['data'] = $deliveries;

        return response($this->response, 200);
    }

    public function getDeliveryById(Request $request, $id)
    {
        $delivery = $this->delivery->findDelivery($id);

        if (empty($delivery)) {
            $this->response['success']  = false;
            $this->response['code']     = '200';
            $this->response['message']  = "Didn't found any delivery !";
            $this->response['data']     = [];
            $this->response['error']    = get_error_response(404, "Didn't find delivery by the id {$id}");

            return response($this->response, 200);
        }

        $delivery->tracking_details = $this->delivery->getDeliveryTrackingLogs($id);
        $this->response['success'] = true;
        $this->response['message']  = "Found deliver by the id {$id}";
        $this->response['code'] = $this->success_code;
        $this->response['data'] = $delivery;
        $this->response['error'] = null;

        return response($this->response, 200);
    }

    public function postStoreDelivery($id = null)
    {
        $msg = "Delivery successfully added !";
        $inputs = Input::json()->all();

        if(!$this->validateDeliveryAddingRequest($inputs)){
            $this->response['success']  = false;
            $this->response['code']     = '422';
            $this->response['message']  = "The given data failed to pass validation !";
            $this->response['data']     = [];
            $this->response['error']    = get_error_response(422, "The given data failed to pass validation !", $this->_errors, $this->getErrorAsString());
            return response($this->response, 200);
        }

        api_logs("delivery/add", "Merchant end", "M_007" , json_encode($inputs), "During trying to add/edit delivery", 'Delivery add ', 007);
        $delivery_id = $this->delivery->postStoreDelivery($inputs, $id);

        if($delivery_id > 0){
            $this->response['success']  = true;
            $this->response['code']     = $this->success_code;
            $this->response['data']     = $delivery_id;
            $this->response['message']  = $id > 0 ? 'Delivery successfully updated !' : $msg;
            $this->response['error']    = null;
            return response($this->response, $this->success_code);
        }

        if ($delivery_id == 'i') {
            $msgs = "Couldn't update the consignment data because it has been already accepted by admin. Please contact with system admin.";
        } else {
            $msgs = "Couldn't add delivery !";
        }

        $this->response['success']  = false;
        $this->response['code']     = $this->error_code;
        $this->response['message']  = $msgs;
        $this->response['data']     = 0;
        $this->response['error']    = get_error_response(422, $msgs, []);

        return response($this->response, $this->error_code);
    }

    protected function validateDeliveryAddingRequest($inputs){
        $validator = Validator::make($inputs, [
            'recipient_name'        => 'required|min:2|max:100',
            'recipient_number'      => 'required|min:2|max:20',
            'recipient_zone_id'     => 'required|numeric',
            'recipient_email'       => 'email',
            'recipient_address'     => 'required|min:2|max:500',
           // 'store_id'              => 'required|numeric',
            'plan_id'               => 'required|numeric'
        ],
        [
            'recipient_zone_id.required' => 'Please select zone !',
            'recipient_zone_id.numeric' => 'Invalid zone selection !',
           // 'store_id.required' => 'Please select store !',
          //  'store_id.numeric' => 'Invalid store selection !',
            'plan_id.required' => 'Please select plan !',
            'plan_id.numeric' => 'Invalid plan selection !',
        ]);

        if ($validator->fails()) {
            $this->_error_single_arr = $validator->errors()->all();
            $this->_errors = $validator->errors()->getMessages();
            return false;
        }

        return true;
    }

    private function getErrorAsString() {
        $errorString ="";
        
        foreach ($this->_error_single_arr as $error) {
            $errorString .= $error.",";
        }

        return rtrim($errorString,',');
    }
}
