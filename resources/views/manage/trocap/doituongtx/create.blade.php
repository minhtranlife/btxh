@extends('main')

@section('custom-style')
    <link type="text/css" rel="stylesheet" href="{{ url('vendors/bootstrap-datepicker/css/datepicker.css') }}">
@stop


@section('custom-script')
    <script type="text/javascript" src="{{url('assets/global/plugins/jquery-validation/js/jquery.validate.min.js')}}"></script>
    <!--cript src="{{url('assets/admin/pages/scripts/form-validation.js')}}"></script-->
    <script src="{{url('minhtran/jquery.inputmask.bundle.min.js')}}"></script>
    <script>
        $(document).ready(function(){
            $(":input").inputmask();
        });
    </script>

@stop

@section('content')
    <h3 class="page-title">
        Chi trả trợ cấp đối tượng thường xuyên<small> thêm mới</small>
    </h3>
    <!-- END PAGE HEADER-->

    <!-- BEGIN DASHBOARD STATS -->
    <div class="row center">
        <div class="col-md-12 center">
            <!-- BEGIN VALIDATION STATES-->
            <div class="portlet box">
                <!--div class="portlet-title">
                </div-->
                <div class="portlet-body form">
                    <!-- BEGIN FORM-->
                    {!! Form::open(['url'=>'trocapdoituongtx', 'id' => 'create_tcdoituongtx', 'class'=>'horizontal-form']) !!}
                        <meta name="csrf-token" content="{{ csrf_token() }}" />

                        @include('manage.trocap.doituongtx.include.thongtin')
                    <!-- END FORM-->
                </div>
            </div>

            <div style="text-align: center">
                <a href="{{url('trocapdoituongtx')}}" class="btn btn-danger"><i class="fa fa-reply"></i>&nbsp;Quay lại</a>
                <button type="reset" class="btn btn-default"><i class="fa fa-refresh"></i>&nbsp;Nhập lại</button>
                <button type="submit" class="btn green"  onclick="validateForm()"><i class="fa fa-check"></i> Hoàn thành</button>
            </div>
            {!! Form::close() !!}
            <!-- END VALIDATION STATES-->
        </div>
    </div>
    <script type="text/javascript">
        function validateForm(){

            var validator = $("#create_tcdoituongtx").validate({
                rules: {
                    ten :"required"
                },
                messages: {
                    ten :"Chưa nhập dữ liệu"
                }
            });


        }
    </script>
    <script>
        $(function(){
            $('#create_tcdoituongtx?? :submit').click(function(){

                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: '/tcdoituongtx/check',
                    type: 'GET',
                    data: {
                        _token: CSRF_TOKEN,
                        thang: $('#thang').val(),
                        nam: $('#nam').val(),
                        mahuyen:  $('select[name="mahuyen"]').val(),
                        maxa:  $('select[name="maxa"]').val(),
                        pltrocap: $('#pltrocap').val()
                    },
                    dataType: 'JSON',
                    success: function (data) {

                        if (data.status == 'success'){
                            $('#create_tcdoituongtx').submit(function (e) {
                                e.preventDefault();
                            });
                        }
                        else {

                            toastr.error("Bạn cần kiểm tra lại thông tin vừa nhập!", "Lỗi!");
                        }
                    }
                })


            });
        });
    </script>
    @include('includes.script.create-header-scripts')
@stop