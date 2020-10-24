@extends('admin.layout.master')
@section('title') Merchant Delivery List @stop

@section('page_name')
    Merchant Management
    <small>Merchant Delivery list</small>
@stop

@section ('breadcrumbs')
    <li> <a href="{!! route('admin.dashboard') !!}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active"> {!! link_to_route('admin.merchants', 'Merchant Management') !!} </li>
@stop

@section('content')
    <div class="m-portlet">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <div class="m-portlet__head-text">
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                <a href="{!! route('admin.merchants') !!}" class="mt-2 btn btn-sm btn-brand m-btn--pill">
                                    <span class="fa fa-arrow-left"></span>&nbsp; Back To Merchant
                                </a>
                            </div>
                            <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                <h5 class="mb-0 m--font-brand">{{ $merchant->first_name.' '.$merchant->last_name}}</h5>
                                <small>{{ $merchant->business_name}}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="m-portlet__head-tools">
                <div class="m-btn-group m-btn-group--pill btn-group-sm btn-group date-show-container export-date-show-container" role="group" aria-label="First group" style="display:none; margin-left: 5px; margin-right: 5px">
                    <span class="m--font-brand"><b>Export from :</b></span> <span class="start-select-date" name=""> </span> <span class="m--font-brand"><b>To</b></span> <span class="end-select-date" name=""> </span>
                </div>

                <div class="m-btn-group m-btn-group--pill btn-group-sm btn-group" role="group" aria-label="First group">
                    <span class="m-btn btn btn-light m-loader m-loader--brand m-loader--right m-loader--lg" id="loading-indicator" style="display: none" ></span>
                </div>

                <div class="m-btn-group m-btn-group--pill btn-group" role="group" aria-label="First group">
                    <button type="button" class="m-btn btn btn-primary export-file" title="Excel" name="xlsx">
                        <i class="fa fa-file-excel-o"></i>
                    </button>
                    <button type="button" class="m-btn btn btn-success export-file" name="csv" title="Csv">
                        <i class="fa fa-file-o"></i>
                    </button>
                    {{--<button type="button" class="m-btn btn btn-brand export-file" title="Pdf" name="pdf">
                        <i class="fa fa-file-pdf-o"></i>
                    </button>--}}
                </div>
            </div>
        </div>
        <div class="m-portlet__body">
            <div class="m-section">
                <div class="m-section__content table-responsive">
                    <table id="merchant-delivery-datatable" class="table table-sm m-table m-table--head-bg-brand table-striped table-hover">
                        <thead class="">
                        <tr>
                            <th>Sl.</th>
                            <th>Consignment Id</th>
                            <th>Delivery Date</th>
                            <th>Merchant Name</th>
                            <th>Recipient Info</th>
                            <th>Zone Name</th>
                            <th>Status</th>
                            <th>Amount to be collected</th>
                            <th>Received Amount</th>
                            <th>Charge</th>
                            <th>COD</th>
                            <th>Payment Status</th>
                            <th>Delivery Created At</th>
                        </tr>
                        </thead>
                        <tfoot id="delivery-footer">
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>

                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="m-portlet__foot clearfix">
            <div class="pull-right">
            </div>
        </div>
        <!--end::Section-->
    </div>

    <!--end::Portlet-->
    <!--end::Modal-->
    <script src="{!! asset('backend/ezzyr_assets/jquery_validator/jquery.validate.js') !!}" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            var table = $('#merchant-delivery-datatable').DataTable({
                processing: true,
                serverSide: true,
                paging: true,
                pageLength: 25,
                lengthChange: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                dom : 'l<"">frtip',
                ajax: {
                    url: '{!! route('admin.datatable.merchant.delivery') !!}',
                    type: 'GET',
                    data: function( d ) {
                        d._token = "{{ csrf_token() }}";
                        d.id = "{{ $merchant->id }}";
                    }
                },
                columns: [
                    {
                        data: 'deli_id',
                        name: 'deli_id',
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'consignment_id',
                        name: 'consignment_id',
                        searchable: true,
                    },
                    {
                        data: 'delivery_date',
                        name: 'delivery_date',
                        searchable: false
                    },
                    {
                        data: 'full_name',
                        name: 'full_name',
                        searchable: true,
                        render: function (data, type, row, meta) {
                            return row.business_name+'<br><small style="color: #716ACA">'+row.full_name+'</small>';
                        }
                    },
                    {
                        data: 'recipient_name',
                        name: 'recipient_name',
                        searchable: true,
                        render: function ( data, type, row ) {
                            return row.recipient_name+'<br><small style="color: #716aca;">'+row.recipient_email+'</small>'+'<br><small style="color: #716aca;">'+row.recipient_number+'</small>';
                        }
                    },
                    {
                        data: 'zone_name',
                        name: 'cz.zone_name',
                        searchable: true

                    },
                    {
                        data: 'flag_text',
                        name: 'flag_text',
                        searchable: false,
                        render: function ( data, type, row ) {
                            var showText = row.flag_text;
                            return '<b style="color: '+row.color_code+';">'+showText+'</b>' +
                                '<input type="hidden" name="consignment_current_status_'+row.deli_id+'" id="consignment_status" value="'+row.status+'">\n'+
                                '<input type="hidden" name="consignment_id_'+row.deli_id+'" id="" value="'+row.consignment_id+'">\n';
                        }
                    },
                    {
                        data: 'amount_to_be_collected',
                        name: 'amount_to_be_collected',
                        searchable: false

                    },
                    {
                        data: 'receive_amount',
                        name: 'receive_amount',
                        searchable: false

                    },
                    {
                        data: 'charge',
                        name: 'charge',
                        searchable: false

                    },
                    {
                        data: 'cod_charge',
                        name: 'cod_charge',
                        searchable: false

                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status',
                        searchable: false,
                        render: function ( data, type, row ) {
                            var showText = '';
                            if(row.payment_status == 0)
                            {
                                showText = 'Unpaid';
                            }else{
                                showText = 'Paid';
                            }
                            return showText;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: false
                    }
                ],

                //Data table footer
                "footerCallback": function ( row, data, start, end, display ) {
                    var api = this.api(), data;

                    // Remove the formatting to get integer data for summation
                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    pageTotalCollectedAmount = api
                        .column( 7, { page: 'current'} )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );

                    pageTotalReceivedAmount = api
                        .column( 8, { page: 'current'} )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );

                    pageTotalCollectedAmount = api
                        .column( 9, { page: 'current'} )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );

                    pageTotalReceivedAmount = api
                        .column( 10, { page: 'current'} )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );


                    // Update footer
                    $( api.column( 7 ).footer() ).html(
                        'Total: ' + Math.round(pageTotalCollectedAmount)
                    );

                    $( api.column( 8 ).footer() ).html(
                        'Total: ' + Math.round(pageTotalReceivedAmount)
                    );

                    $( api.column( 9 ).footer() ).html(
                        'Total: ' + Math.round(pageTotalCollectedAmount)
                    );

                    $( api.column( 10 ).footer() ).html(
                        'Total: ' + Math.round(pageTotalReceivedAmount)
                    );

                }
            });

            $('.dataTables_wrapper').removeClass('form-inline');
            $('.form-control').removeClass('input-sm');
            $('.dataTables_processing').addClass('m-loader m-loader--brand');

            $('#merchant-delivery-datatable_length').addClass('col-lg-4 col-md-4 col-sm-4');
            $('#merchant-datatable_length .form-control').addClass('m-bootstrap-select filter-select');
            $('#date-filter').addClass('col-lg-3 col-md-3 col-sm-3');
            $('#merchant-datatable_filter').addClass('col-lg-3 col-md-3 col-sm-3');
            $('.dataTables_wrapper .dt-buttons').addClass('col-lg-2 col-md-2 col-sm-2 float-right');

            var date_picker_html =
                '<div class="form-group m-form__group clearfix">' +
                '<div class="input-group pull-right" id="m_daterangepicker">' +
                '<input type="text" class="form-control m-input" readonly  placeholder="Select date range" value=""/>' +
                '<span class="input-group-addon">' +
                '<i class="fa fa-calendar"></i>' +
                '</span>' +
                '</div>' +
                '</div>';

            $('#date-filter').append(date_picker_html);
            $('#merchant-datatable_filter label:first-child input').attr('placeholder', 'Name, mobile, email,Business name');


            $('#m_daterangepicker').daterangepicker({
                buttonClasses: 'm-btn btn',
                applyClass: 'btn-primary',
                cancelClass: 'btn-secondary',
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment(),
                endDate: moment(),
                format: 'YYYY-MM-DD'

            }, function(start, end, label) {
                $('#m_daterangepicker .form-control').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
                var start_date = start.format('YYYY-MM-DD');
                var end_date = end.format('YYYY-MM-DD');
                table.columns(9).search(start_date + ' ~ ' + end_date).draw();


                // To view beside export buttons
                $('.date-show-container').show();
                $('.start-select-date').html(start.format('DD-MM-YYYY'));
                $('.start-select-date').attr('name', start.format('YYYY-MM-DD'));
                $('.end-select-date').html(end.format('DD-MM-YYYY'));
                $('.end-select-date').attr('name', end.format('YYYY-MM-DD'));
            });

            //For clear the input field
            function tog(v){
                return v?'addClass':'removeClass';
            }
            $(document).on('input', '.clearable', function(){
                $(this)[tog(this.value)]('x');
            }).on('change', '.clearable', function( e ){
                $(this)[tog(this.value)]('x');
            }).on('mousemove', '.x', function( e ){
                $(this)[tog(this.offsetWidth-18 < e.clientX-this.getBoundingClientRect().left)]('onX');
            }).on('touchstart click', '.onX', function( ev ){
                ev.preventDefault();
                $(this).removeClass('x onX').val('').change();
                table.columns(9).search('').draw();
            });

        });

    </script>
    <script>
        // For export data
        $(document).on('click','.export-file',function(e)
        {
            e.preventDefault();
            var base_url = "{{ url('/') }}";
            var exportType = $(this).attr('name');
            var startDate = $('.start-select-date').attr('name');
            var endDate = $('.end-select-date').attr('name');
            var merchant_id = {{$merchant->id}};

            console.log(merchant_id);

            $.ajax({
                type: "POST",
                url: "{!! route('admin.merchants.deliveries.export') !!}",
                data: {"_token": "{{ csrf_token() }}", "export_type": exportType, "start_date": startDate, "end_date": endDate, "merchant_id": merchant_id},
                cache: false,
                beforeSend: function(){
                    $('#loading-indicator').show();
                },
                complete: function(){
                    $('#loading-indicator').hide();
                },
                success: function (res) {
                    if (res.success === false) {
                        alert(res.msg);
                    } else {
                        location.href = base_url +'/'+ res.full;
                    }
                }
            });

        });
    </script>
    <script>
        //== Class definition
        var Selectpick = function() {
            //== Private functions
            var demos = function() {
                // basic
                $('.filter-select').selectpicker();
            };

            //== Public functions
            return {
                init: function() {
                    demos();
                }
            };
        }();

        //== Initialization
        jQuery(document).ready(function() {
            Selectpick.init();
        });
    </script>

    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button{
            border:0px!important;
            padding:0px!important;
        }
        .dataTables_length{
            margin-bottom:10px;
        }
        .dataTables_length .form-control {
            margin-left: 5px;
            margin-right: 5px;
        }
        table.dataTable.no-footer {
            border-bottom:1px solid #f4f4f4!important;
        }
    </style>
@stop
