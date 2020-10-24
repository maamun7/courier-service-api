<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\Api\UtilsApi\UtilsApiRepository;
use Illuminate\Support\Facades\Input;
use Validator;
use DB;


class UtilsApiController extends Controller
{
    protected $utilsApi;

    function __construct(UtilsApiRepository $utilsApi)
    {
        $this->utilsApi = $utilsApi;
        date_default_timezone_set('Asia/Dhaka');

    }

    public function getPaymentMethod()
    {

        $data = $this->utilsApi->getActivePaymentMethods();
        if (empty($data)) {
            $this->response['success']  = false;
            $this->response['code']     = '404';
            $this->response['message']  = "Didn't found any payment method";
            $this->response['error']    = get_error_response(404, "Didn't found any payment method");
            return response($this->response, 404);
        }
        return response($data, 200);

    }

    public function siteSettings(Request $request)
    {
        $inputs = Input::json()->all();

        if(!$this->validationRequest($inputs)){
            $this->response['success']  = false;
            $this->response['code']     = '200';
            $this->response['message']  = "Your request is not valid";
            $this->response['error']    = get_error_response(200,  $this->getErrorAsString());

            return response($this->response);
        }

        $data = $this->utilsApi->getSiteSettings($inputs['driver_id'], $inputs['vehicle_id']);
        if (empty($data)) {
            $this->response['success']  = false;
            $this->response['code']     = '404';
            $this->response['message']  = "Didn't found any settings";
            $this->response['error']    = get_error_response(200, "Didn't found any settings");
            return response($this->response, 200);
        }
        return response($data, 200);

    }    
    
    public function postSendNotification(Request $request)
    {
        $inputs = Input::json()->all();        
        $passenger_id = $inputs['passenger_id'];
        
        if(!$this->validationContentId($inputs)){
            $this->response['success']  = false;
            $this->response['code']     = '200';
            $this->response['message']  = "Your request is not valid";
            $this->response['error']    = get_error_response(200,  $this->getErrorAsString());
            return response($this->response);
        }

        $member_id  = get_member_id_by_passenger_id($passenger_id);
        $device_id  = get_device_id_by_member_id($member_id);
        $is_send_notification   = false;
        $notification_msg       = [];
        
        $this->response['success'] = true;
        $this->response['message'] = 'Successfully cancel request';

        if ($device_id != '') { 
            
            $notification_msg['action'] = 'new_content_notification';
            $message = [];
            $message['headline']    = "Celebrate with EZZYR Firework Fares";            
            $message['subhead']     = "PROMOTIONS";
            $message['brief']       = "Every year, Diwali brings immense happiness to all of us – and the biggest reason for this is the company of our loved ones. The candles light up our homes but being with our close ones light up our hearts.";
            $message['content_id']  = rand(1,100); 
            
            $notification_msg['msg'] = $message;
            $is_send_notification = send_push_notification('rider_key', $device_id, json_encode($notification_msg));        

            if($is_send_notification == false)
            {
                $this->response['success'] = false;
                $this->response['message'] = 'Suggetion: Old Device Registration ID Found.Please Update Device Registration ID.';
            }
        }
        
        $this->response['send_push_notification'] = $is_send_notification;       
        
        return response($this->response, 200);     

    }
    
    public function getContentList()
    {
        //$htmlText = $this->demohtml2();
        //$htmlText = "It’s raining happy hours with UBER the whole month! For all you people who enjoy having their drinks by the bay, we have partnered with the Palm Beach Hotel to bring to you a 1+1 offer (flat 50% off ) on your drinks bill. With exemplary service and beautifully executed classically-styled drinks accompanied by a great mix of music in the background, you guys are in for a real treat! Your UBER is just a tap away, lets go.";
        //$htmlText .= "<p><a href='https://www.uber.com/en-IN/blog/visakhapatnam/vizag-your-happy-hours-are-here/'>Read More</a></p>";
        
        $arData = array();
        //$arData['headline'] = addslashes(trim("Most Exclisive HTML Content For You"));
        //$arData['sub_head'] = addslashes("DEVELOPER");
       // $arData['image_url'] = "";
        //$arData['content'] = addslashes(trim($htmlText));
        //$arData['publish_date'] = date('Y-m-d H:i:s');
        //$arData['is_published'] = 1;
        //$arData['content_type'] = 3;
        
        
        $data = $this->utilsApi->storeAndReturnWebContentList($arData);
        if (empty($data)) {
            $this->response['success']  = false;
            $this->response['code']     = '200';
            $this->response['message']  = "Didn't found any settings";
            $this->response['error']    = get_error_response(200, "Didn't found any settings");
            return response($this->response, 200);
        }
        
        $this->response['success']  = true;
        $this->response['code']     = '200';
        $this->response['data']     = $data;
        return response($this->response, 200);
        
        
    }


    
    protected function validationContentId($inputs){
        $validator = Validator::make($inputs, [
            'passenger_id'  => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $this->_errors = $validator->errors()->all();
            return false;
        }
        return true;
    }
    protected function validationRequest($inputs){
        $validator = Validator::make($inputs, [
            'driver_id'   => 'required|numeric',
            'vehicle_id'  => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $this->_errors = $validator->errors()->all();
            return false;
        }

        return true;
    }

    
    public function getDriverOnOff(Request $request, $driver_id)
    {
        $data = DB::table('drivers')
            ->select('is_offline')
            ->where('id', $driver_id)
            ->first();
        if (empty($data)) {
            $this->response['success'] = false;
            $this->response['code'] = '200';
            $this->response['message'] = "Didn't found any driver by id id {$driver_id}";
            $this->response['error'] = get_error_response(200, "Didn't found any driver by id id {$driver_id}");
            return response($this->response, 200);
        }

        $this->response['success']      = true;
        $this->response['code']         = '200';
        $this->response['status']       = ($data->is_offline == 0) ? "Online" : "Offline";
        $this->response['status_value'] = $data->is_offline;
        return response($this->response, 200);
    }

    public function postDriverOnOff(Request $request)
    {
        $inputs = Input::json()->all();

        if(!$this->validationDriverOnOffRequest($inputs)){
            $this->response['success']      = false;
            $this->response['code']         = '200';
            $this->response['message']      = "Your request is not valid";
            $this->response['error']        = get_error_response(200,  $this->getErrorAsString());
            return response($this->response);
        }

        $is_save = $this->utilsApi->postDriverOnOff($inputs['driver_id'], $inputs['value']);
       
        if ($is_save) {
            $this->response['success']          = true;
            $this->response['current_status']   = $inputs['value'];
            $this->response['message']          = 'Successfully change the driver on/off status';
            return response($this->response, 200);
        }

        $this->response['success']      = false;
        $this->response['code']         = '200';
        $this->response['message']      = "Couldn't change the driver on/off status";
        $this->response['error']        = get_error_response(200, "Couldn't change the driver on/off status, please check your request format");
       
        return response($this->response);

    }

    protected function validationDriverOnOffRequest($inputs){
        $validator = Validator::make($inputs, [
            'driver_id'   => 'required|numeric',
            'value'       => 'required|numeric|between:0,1'
        ]);

        if ($validator->fails()) {
            $this->_errors = $validator->errors()->all();
            return false;
        }

        return true;
    }

    public function postCalculateDefaultRent(Request $request)
    {
        date_default_timezone_set('Asia/Dhaka');
        $final_price = 0;
        $check_next_discount = true;
        $promo_response = array('promo_code' => "", 'promo_amount' => 0);
        $inputs = Input::json()->all();
                
        if (!$this->validationDefaultRentCalculateRequest($inputs)) {
            $this->response['success']      = false;
            $this->response['code']         = '200';
            $this->response['message']      = "Your request is not valid";
            $this->response['error']        = get_error_response(200, $this->getErrorAsString());
            return response($this->response);
        }

        $distance = $inputs['distance'];
        $waiting_min = $inputs['waiting_min'];
        
        if ($distance <= 0 && $waiting_min) {
            return $final_price;
        }

        $settings = $this->utilsApi->getDefaultSettings();
        $settingsMain = get_main_settings('apply_minimum_fare');
        
        $field_prefix = 'pk';
        $now = strtotime(date('H:i:s'));
        /*if ($now < strtotime('8:00:00') || $now > strtotime('20:00:00')){*/
        if ($now >= strtotime('16:00:00') && $now <= strtotime('22:00:00')){
            $field_prefix = 'opk'; 
        }

        $estPriceList = array();
        $this->success_code = 200;
        if (! empty($settings)) {
            foreach ($settings as $value) {
                                
                $value = $this->object_to_array($value);
                
                $base_fare = ($value["{$field_prefix}_base_fare"] <= 0) ? 0 : $value["{$field_prefix}_base_fare"];
                $unit_fare = ($value["{$field_prefix}_unit_fare"] <= 0) ? 0 : $value["{$field_prefix}_unit_fare"];                                
                $wtn_min_charge = ($value["{$field_prefix}_wtng_min_charge"] <= 0) ? 0 : $value["{$field_prefix}_wtng_min_charge"];
                
                $final_price = $base_fare + ($distance * $unit_fare) + ($waiting_min * $wtn_min_charge);
                $final_price = floor($final_price);
                
                //Go for promo discount amount
                if(array_key_exists('passenger_id', $inputs) && $check_next_discount)
                {
                    $promo_response = $this->utilsApi->passengerPromoDiscount($final_price, $value['id'], $inputs['passenger_id']);                    
                }
                
                $estPriceList[$value["id"]]['vehicle_type_id']  = $value["id"];
                $estPriceList[$value["id"]]['vehicle_type']     = $value["type_name"];
                $estPriceList[$value["id"]]['no_promo_price']   = number_format($final_price,2);
                $estPriceList[$value["id"]]['total_price']      = ($promo_response['promo_amount'] == 0? number_format($final_price,2) : number_format(($final_price - $promo_response['promo_amount']),2));
                $estPriceList[$value["id"]]['promo_price']      = ($promo_response['promo_amount'] == 0? number_format($final_price,2) : number_format(($final_price - $promo_response['promo_amount']),2));
                $estPriceList[$value["id"]]['is_promo_applied'] = ($promo_response['promo_amount'] == 0? false  : true );
                $estPriceList[$value["id"]]['promo_code']       = ($promo_response['promo_amount'] == 0? 0 : $promo_response['promo_code'] );
                $estPriceList[$value["id"]]['promo_amount']     = ($promo_response['promo_amount'] == 0? 0 : number_format($promo_response['promo_amount'],2) );
		$estPriceList[$value["id"]]['time']             = date("Y-m-d", strtotime('2017-12-18'));
                //$this->array_to_object($estPriceList[$value["id"]]);


                if($settingsMain['apply_minimum_fare'] == 1)
                {
                    if($value['min_fare'] > $estPriceList[$value["id"]]['total_price'])
                    {
                        $farediff               = $value['min_fare'] - $estPriceList[$value["id"]]['total_price'];
                        $new_promo_amount       = $estPriceList[$value["id"]]['promo_amount'] - $farediff;                
                        
                        $estPriceList[$value["id"]]['promo_amount']     = $new_promo_amount > 0 ? $new_promo_amount: 0;
                        $estPriceList[$value["id"]]['total_price']      = $value['min_fare'];
                        $estPriceList[$value["id"]]['promo_price']      = $value['min_fare'];
                        $estPriceList[$value["id"]]['no_promo_price']   = $value['min_fare'];
                    }
                }
            }
            
            //prixt($settings,1);

            $data['success'] = true;
            $data['code'] = 200;
            $data['data'] = array($estPriceList[1],$estPriceList[2]);
            return response($data, $this->success_code);
        }

        $this->response['success']  = false;
        $this->response['code']     = '200';
        $this->response['message']  = "Couldn't found settings";
        $this->response['error']    = get_error_response(200, "Couldn't found for this vehicle, please check your request format");

        return response($this->response);

    }

    protected function validationDefaultRentCalculateRequest($inputs){
        $validator = Validator::make($inputs, [
            'distance'    => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $this->_errors = $validator->errors()->all();

            return false;
        }

        return true;
    }


    public function postCalculateDefaultRentDev(Request $request)
    {
        date_default_timezone_set('Asia/Dhaka');
        $final_price = 0;
        $check_next_discount = true;
        $promo_response = array('promo_code' => "", 'promo_amount' => 0);
        $inputs = Input::json()->all();
                
        if (!$this->validationDefaultRentCalculateRequest($inputs)) {
            $this->response['success']      = false;
            $this->response['code']         = '200';
            $this->response['message']      = "Your request is not valid";
            $this->response['error']        = get_error_response(200, $this->getErrorAsString());

            return response($this->response);
        }

        $distance = $inputs['distance'];
        $waiting_min = $inputs['waiting_min'];
        
        if ($distance <= 0 && $waiting_min) {

            return $final_price;
        }

        $settings = $this->utilsApi->getDefaultSettingsDev();
        
        
        $field_prefix = 'pk';
        $now = strtotime(date('H:i:s'));
        /*if ($now < strtotime('8:00:00') || $now > strtotime('20:00:00')){*/
        if ($now >= strtotime('16:00:00') && $now <= strtotime('22:00:00')){
            $field_prefix = 'opk'; 
        }
        
        $this->success_code = 200;

        if (! empty($settings)) {
            
            $data3 = [];   
            foreach ($settings as $vehi) {     
                $data2 = [];   
                foreach ($vehi->vehicle_types as $value) {
                    $value = $this->object_to_array($value);
                    $estPriceList = (object) null;
                    //$estPriceList = [];
                
                    $base_fare = ($value["{$field_prefix}_base_fare"] <= 0) ? 0 : $value["{$field_prefix}_base_fare"];
                    $unit_fare = ($value["{$field_prefix}_unit_fare"] <= 0) ? 0 : $value["{$field_prefix}_unit_fare"];                                
                    $wtn_min_charge = ($value["{$field_prefix}_wtng_min_charge"] <= 0) ? 0 : $value["{$field_prefix}_wtng_min_charge"];
                    
                    $final_price = $base_fare + ($distance * $unit_fare) + ($waiting_min * $wtn_min_charge);
                    $final_price = floor($final_price);
                    
                    //Go for promo discount amount
                    if(array_key_exists('passenger_id', $inputs) && $check_next_discount)
                    {
                        $promo_response = $this->utilsApi->passengerPromoDiscount($final_price, $value['id'], $inputs['passenger_id']);                    
                    }
                    
                    $estPriceList->vehicle_type_id  = $value["id"];
                    $estPriceList->vehicle_type     = $value["type_name"];                    
                    $estPriceList->image_url        = $value["type_image_url"];
                    $estPriceList->select_image_url = $value["select_image_url"];
                    $estPriceList->capacity = $value["capacity"];
                    $estPriceList->no_promo_price   = number_format($final_price,2);
                    $estPriceList->total_price      = ($promo_response['promo_amount'] == 0? number_format($final_price,2) : number_format(($final_price - $promo_response['promo_amount']),2));
                    $estPriceList->promo_price      = ($promo_response['promo_amount'] == 0? number_format($final_price,2) : number_format(($final_price - $promo_response['promo_amount']),2));
                    $estPriceList->is_promo_applied = ($promo_response['promo_amount'] == 0? false  : true );
                    $estPriceList->promo_code       = ($promo_response['promo_amount'] == 0? 0 : $promo_response['promo_code'] );
                    $estPriceListpromo_amount       = ($promo_response['promo_amount'] == 0? 0 : number_format($promo_response['promo_amount'],2) );
                    $estPriceList->time             = date("Y-m-d", strtotime('2017-12-18'));
                    //$this->array_to_object($estPriceList[$value["id"]]);

                    //$value->estimation = $estPriceList;

                    $data2[] = $estPriceList;

                    
                }
                
                $vehi->vehicle_types = $data2;
                $data3[] = $vehi;
                
            }
            
            //prixt($data,1);

            $data['success'] = true;
            $data['code'] = 200;
            //$data['data'] = array($estPriceList[1],$estPriceList[2]);
            $data['data'] = $data3;
            return response($data, $this->success_code);
        }
        $this->response['success']  = false;
        $this->response['code']     = '200';
        $this->response['message']  = "Couldn't found settings";
        $this->response['error']    = get_error_response(200, "Couldn't found for this vehicle, please check your request format");

        return response($this->response);

    }



    public function postRentCalculate(Request $request)
    {
        $final_price = 0;
        $inputs = Input::json()->all();

        if(!$this->validationRentCalculateRequest($inputs)){
            $this->response['success']      = false;
            $this->response['code']         = '200';
            $this->response['message']      = "Your request is not valid";
            $this->response['error']        = get_error_response(200,  $this->getErrorAsString());
            return response($this->response);
        }

        $settings = $this->utilsApi->getVehicleSettings($inputs['driver_id'], $inputs['vehicle_id']);

        if (! empty($settings)) {
            $base_distance  = ($settings->base_distance <= 0) ? 0 : $settings->base_distance;
            $base_price     = ($settings->base_price <= 0) ? 0 : $settings->base_price;
            $unit_price     = ($settings->unit_price <= 0) ? 0 : $settings->unit_price;

            $distance = $inputs['distance'];
            if ($distance <= 0) {
                $final_price = 0;
            } elseif ($distance <= $base_distance) {
                $final_price = $base_price;
            } else {
                $final_price =  $base_price + (($distance - $base_distance) * $unit_price);
            }

            $this->response['success']      = true;
            $this->response['total_price']  = $final_price;

            return response($this->response, 200);
        }

        $this->response['success']      = false;
        $this->response['code']         = '200';
        $this->response['message']      =  "Couldn't found settings for this vehicle";
        $this->response['error']        = get_error_response(200, "Couldn't found settings for this vehicle, please check your request format");
        return response($this->response);

    }

    protected function validationRentCalculateRequest($inputs){
        $validator = Validator::make($inputs, [
            'driver_id'   => 'required|numeric',
            'vehicle_id'  => 'required|numeric',
            'distance'    => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $this->_errors = $validator->errors()->all();
            return false;
        }
        return true;
    }


    public function postEmergencyVehicleInCityDev(Request $request)
    {
        date_default_timezone_set('Asia/Dhaka');       
        $inputs = Input::json()->all();

        $arVehicles = $this->utilsApi->getEmergencyVehicleInCity($inputs);
        
                
        if (!$this->validationEmergencyVehicleInCityRequest($inputs)) {
            $this->response['success']      = false;
            $this->response['code']         = '200';
            $this->response['message']      = "Your request is not valid";
            $this->response['error']        = get_error_response(200, $this->getErrorAsString());
            return response($this->response);
        }

        

        if (! empty($arVehicles)) {
            $this->response['success']      = true;
            $this->response['data']  = $arVehicles['data'];
            return response($this->response, 200);

        }


        $this->response['success']  = false;
        $this->response['code']     = '200';
        $this->response['message']  = "Couldn't found Any Emmergency Vehicle";
        $this->response['error']    = get_error_response(200, "Couldn't found Any Emmergency Vehicle");
        return response($this->response);

    }

    protected function validationEmergencyVehicleInCityRequest($inputs){
        $validator = Validator::make($inputs, [
            'city_id'    => 'required|numeric',
            'vahicle_group_id'    => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $this->_errors = $validator->errors()->all();
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    private function getErrorAsString(){
        $errorString ="";
        foreach ($this->_errors as $error) {
            $errorString .= $error.",";
        }
        return $errorString;
    }
    /*
     *PHP Array into a PHP Object
     */
    private function array_to_object($array) {
        return (object) $array;
    }
    /*
     *PHP Object into a PHP Array
     */
    private function object_to_array($object) {
        return (array) $object;
    } 
        
    /*****Need to check this getRatingReport*****/
    public function getRatingReport(Request $request)
    {
        $data = $this->utilsApi->getDriverPromotion();
        if(empty($data)){
            $this->response['success']      = false;
            $this->response['code']         = 200;
            $this->response['message']      = "Didn't found promotion";
            $this->response['error']        = get_error_response($this->error_code, "Didn't found promotion");

            return response($this->response, $this->error_code);
        }

        $data['success'] = true;
        $data['code'] = 200;

        return response($data, 200);
    }
    
    public function postAppSettings(Request $request)
    {
        $inputs = Input::json()->all();
        
        if (empty($inputs)&& $inputs['keys'] == "") {            
            $this->response['success']      = false;
            $this->response['code']         = '200';
            $this->response['message']      = "Your request is not valid";
            $this->response['error']        = get_error_response(200,  $this->getErrorAsString());

            return response($this->response);               
        }
        
	    data = $this->utilsApi->getAppSettings($inputs['keys']);         
	
        if (empty($data)) {
            $this->response['success']      = false;
            $this->response['code']         = '404';
            $this->response['message']      = "Didn't found any settings";
            $this->response['error']        = get_error_response(200, "Didn't found any settings");
            return response($this->response, 200);
        }

        return response($data, 200);

    }
}
