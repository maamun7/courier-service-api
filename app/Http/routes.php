<?php
//Route::get('/', function () {
//    if (Auth::user()) {
////        if (get_logged_user_type() == '0') {
////            return redirect('/admin');
////        }
//        return redirect('/dashboard');
//    }
//    return view('agent.auth.login');
//});

Route::group(['prefix' => 'v1',  'namespace' => 'Api' ], function()
{
    require_once(__DIR__ . "/Routes/Api.php");
});
