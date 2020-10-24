@extends('admin.layout.master')
@section('title')Invoice  @stop


@section('page_name')
    Invoice  Management
    <small>Invoice Details</small>
@stop

@section ('breadcrumbs')
    <li> <a href="{!! route('admin.dashboard') !!}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active"> {!! link_to_route('admin.agents', 'Invoice  Management') !!} </li>
@stop

@section('content')
    <style>
        .invoice-title h2, .invoice-title h3 {
            display: inline-block;
        }

        .table > tbody > tr > .no-line {
            border-top: none;
        }

        .table > thead > tr > .no-line {
            border-bottom: none;
        }

        .table > tbody > tr > .thick-line {
            border-top: 2px solid;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            #section-to-print, #section-to-print * {
                visibility: visible;
            }
            #section-to-print {
                position: absolute;
                left: 0;
                top: 0;
            }
            #section-to-print #ddd{
                visibility: hidden;
            }
        }
    </style>
    <?php
        $subTotal = 0;
        $total_charge = 0;
        $total = 0;
        $cod_charge = 0;
        $delivery_charge = 0;
    ?>
    <!--begin::Portlet-->
    <div class="m-portlet">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <span class="m-portlet__head-icon m--font-brand">
                        <i class="fa fa-edit"></i>
                    </span>
                    <h3 class="m-portlet__head-text">
                        Invoice
                    </h3>

                </div>
            </div>
            <div class="m-portlet__head-tools">
                {{--<input type = "button" value = "Print" onclick = "window.print()" />--}}
                <span class="btn btn-brand m-btn m-btn--icon btn-lg m-btn--icon-only m-btn--pill m-btn--air" title="Print" onclick = "window.print()">
                    <i class="fa fa-print"></i>
                </span>
            </div>
        </div>
        <!--begin::Form-->
        <form method="post" action="{{route('admin.invoice.note.store')}}">
            {{csrf_field()}}
            <input type="hidden" name="invoice_date" value="{{$invoice_date}}">
            <input type="hidden" name="merchant_id" value="{{$merchant->id}}">
            <div class="m-form m-form--fit m-form--label-align-right ">
                <div class="m-portlet__body" id="section-to-print">
                    <div class="row">
                        <div class="col-sm-12 col-md-12 col-md-12 col-lg-12 text-center">
                            <img src="{!! asset('backend/ezzyr_assets/app/media/img/logos/logo.png') !!}" width="250px">
                        </div>
                    </div>
                    <div class="row" style="padding: 35px;">
                        <div class="col-md-12">
                            <div class="invoice-title">
                                @if($_GET['invoice_type'] == "unpaid")
                                    <h2>Invoice </h2><h5 class="pull-right">Last Invoice Date: {{$last_invoice_date}}</h5>
                                @else
                                    <h2>Invoice </h2><h5 class="pull-right">Date: {{$paid_invoice_date}}</h5>
                                @endif
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-xs-6">
                                    <address>
                                        <b>Merchant Info:</b><br>
                                        {{$merchant->first_name." ".$merchant->last_name}}<br>
                                        {{$merchant->business_name}}<br>
                                        {{$merchant->address}}<br>
                                    </address>
                                </div>
                                <div class="col-xs-6 text-right" style="padding-right: 15px;">
                                    <address>
                                        <h1 style="color: {{$_GET['invoice_type'] == 'unpaid' ? '#AB1803':'#036417' }}">{{$_GET['invoice_type'] == "unpaid" ? 'UNPAID' : 'PAID'}}</h1>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="padding: 35px;">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><strong>Order summary</strong></h3>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-condensed">
                                            <thead>
                                            <tr>
                                                <th class="text-left"><strong>Consignment ID</strong></th>
                                                <th class="text-center"><strong>Merchant Order ID</strong></th>
                                                <th class="text-center"><strong>Amount to be collected</strong></th>
                                                <th class="text-center"><strong>Received Amount</strong></th>
                                                <th class="text-center"><strong>Delivery Charge</strong></th>
                                                <th class="text-center"><strong>COD Charge</strong></th>
                                                <th class="text-center"><strong>Recipient Name</strong></th>
                                                <th class="text-center"><strong>Package Entry date</strong></th>
                                                <th class="text-right"><strong>Totals</strong></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <!-- foreach ($order->lineItems as $line) or some such thing here -->
                                            @forelse($invoices as $delivery)
                                                <?php
                                                $total = $total + ($delivery->receive_amount - ($delivery->charge + $delivery->cod_charge));
                                                $total_charge = $total_charge + $delivery->charge;
                                                $cod_charge = $cod_charge + $delivery->cod_charge;
                                                $delivery_charge = $delivery_charge + $delivery->charge;
                                                ?>
                                                <tr>
                                                    <input type="hidden" name="delivery_id[]" value="{{$delivery->id}}">
                                                    <td class="text-left">{{$delivery->consignment_id}}</td>
                                                    <td class="text-center">{{!empty($delivery->merchant_order_id) ? $delivery->merchant_order_id : '-----'}}</td>
                                                    <td class="text-center">{{$delivery->amount_to_be_collected}}</td>
                                                    <td class="text-center">{{$delivery->receive_amount}}</td>
                                                    <td class="text-center">{{$delivery->charge}}</td>
                                                    <td class="text-center">{{$delivery->cod_charge}}</td>
                                                    <td class="text-center">{{$delivery->recipient_name}}</td>
                                                    <td class="text-center">{{date('M j, Y', strtotime($delivery->created_at))}}</td>
                                                    <td class="text-right">{{$delivery->receive_amount - ($delivery->charge + $delivery->cod_charge)}}</td>
                                                </tr>
                                            @empty
                                                <p></p>
                                            @endforelse
                                            <tr>
                                                <td class="thick-line"></td>
                                                <td class="thick-line"></td>
                                                <td class="thick-line"></td>
                                                <td class="thick-line"></td>
                                                <td class="thick-line text-center text-bold"><b>{{$delivery_charge}}</b></td>
                                                <td class="thick-line text-center text-bold"> <b>{{$cod_charge}}</b></td>
                                                <td class="thick-line text-center"><strong></strong></td>
                                                <td class="thick-line text-center"><strong></strong></td>
                                                <td class="thick-line"></td>
                                            </tr>

                                            <tr>
                                                <td class="no-line"></td>
                                                <td class="no-line"></td>
                                                <td class="no-line"></td>
                                                <td class="no-line"></td>
                                                <td class="no-line"></td>
                                                <td class="no-line text-center"></td>
                                                <td class="no-line text-center"> </td>
                                                <td class="no-line text-center"><strong>Total</strong></td>
                                                <td class="no-line text-right text-bold"> <b>BDT {{$total}}</b></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12" id="ddd">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><strong>Activity</strong></h3>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea name="notes" class="form-control" rows="4" placeholder="Invoice Notes ...">{!! !empty($inv_notes) ? $inv_notes->notes : '' !!}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Pay to merchant (BDT)</label>
                                        <input type="text" class="form-control" {{$_GET['invoice_type'] == "paid" ? "readonly" : ""}} style="width: 30%;" placeholder="Amount" name="amount" value="{!!  $total !!}">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__foot m-portlet__no-border m-portlet__foot--fit">
                    <div class="m-form__actions m-form__actions--solid">
                        <div class="row">
                            <?php if ($_GET['invoice_type'] == "unpaid") { ?>
                            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 col-lg-offser-2 col-md-offset-2 col-sm-offset-2">
                                <div class="pull-left">
                                    <button class="btn btn-default" tabindex="20">
                                        <i class="fa fa-check-circle text-success fa-lg" aria-hidden="true"></i>
                                        <span class="text-success"> Done </span>
                                    </button>
                                    &nbsp;
                                    <a href="{!! route('admin.invoices') !!}" class="btn btn-default" tabindex="20">
                                        {{-- <span class="glyphicon glyphicon-remove text-danger"></span>&nbsp; <span class="text-danger"> Cancel </span>--}}
                                        <i class="fa fa-remove text-danger fa-lg" aria-hidden="true"></i>&nbsp; <span class="text-danger"> Cancel </span>
                                    </a>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <!--end::Form-->
    </div>
    <!--end::Portlet-->
    
@stop