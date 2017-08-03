@extends('main')

@section('custom-style')
    <link rel="stylesheet" type="text/css" href="{{url('assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{url('assets/global/plugins/select2/select2.css')}}"/>
    <!-- END THEME STYLES -->
@stop


@section('custom-script')
    <!-- BEGIN PAGE LEVEL PLUGINS -->

    <script type="text/javascript" src="{{url('assets/global/plugins/select2/select2.min.js')}}"></script>
    <script type="text/javascript" src="{{url('assets/global/plugins/datatables/media/js/jquery.dataTables.min.js')}}"></script>
    <script type="text/javascript" src="{{url('assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js')}}"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <script src="{{url('assets/admin/pages/scripts/table-managed.js')}}"></script>
    <script>
        jQuery(document).ready(function() {
            TableManaged.init();
        });
        function getId(id){
            document.getElementById("iddelete").value=id;
        }
        function getIdTraLai(id){
            document.getElementById("idtralai").value=id;
        }
        function getIdChuyen(id){
            document.getElementById("idchuyen").value=id;
        }
        function getIdDuyet(id){
            document.getElementById("idduyet").value=id;
        }
        function ClickChuyen(){
            $('#frm_chuyen').submit();
        }
        function ClickDelete(){
            $('#frm_delete').submit();
        }
        function ClickDuyet(){
            $('#frm_duyet').submit();
        }
        function ClickTraLai(){
            if($('#lydotralai').val() == ''){
                toastr.error("Bạn cần nhập lý do trả lại hồ sơ", "Lỗi!!!");
                $("#frm_tralai").submit(function (e){
                    e.preventDefault();
                });
            }else{
                $("#frm_tralai").unbind('submit').submit();
            }
        }

        function ShowLyDo(id) {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            //alert(id);
            $.ajax({
                url: 'ajax/lydodttx',
                type: 'GET',
                data: {
                    _token: CSRF_TOKEN,
                    id: id
                },
                dataType: 'JSON',
                success: function (data) {
                    if(data.status == 'success') {
                        $('#lydo').replaceWith(data.message);
                    }
                }
            })
        }
    </script>
@stop

@section('content')
    <h3 class="page-title">
        Danh sách đối tượng chi trả<small>&nbsp;thường xuyên</small>
    </h3>
    <!-- END PAGE HEADER-->
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet box">

                <div class="portlet-title">
                    <div class="caption">
                    </div>
                    <div class="actions">
                        <a href="{{url('danhsachdoituongtx/'.$trocap.'/create')}}" class="btn btn-default btn-xs mbs"><i class="fa fa-plus"></i>&nbsp;Thêm mới</a>
                    </div>
                </div>

                <div class="portlet-body">
                    <div class="row mbm">
                        <div class="col-md-6">
                            <select id="select_trocap" class="form-control">
                                <option value="NXH" {{$trocap == 'NXH' ? 'selected' : ''}}>Đối tượng BTXH sống trong nhà xã hội tại cộng đồng do xã, phường quản lý</option>
                                <option value="CD" {{$trocap == 'CD' ? 'selected' : ''}}>Đối tượng BTXH tại cộng đồng do xã, phường quản lý</option>
                                <option value="CS" {{$trocap == 'CS' ? 'selected' : ''}}>Đối tượng bảo trợ xã hội sống trong các cơ sở bảo trợ xã hội</option>
                            </select>
                        </div>
                        @if(session('admin')->level == 'T')
                            <div class="col-md-3">
                                <select id="select_huyen" class="form-control">
                                    @foreach ($huyens as $huyen)
                                        <option {{ ($huyen->mahuyen == $mahuyen) ? 'selected' : '' }} value="{{ $huyen->mahuyen }}">{{ $huyen->tenhuyen }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        @if(count($xas) > 0 && (session('admin')->level == 'T' || session('admin')->level == 'H'))
                            <div class="col-md-3">
                                @if(count($xas) > 0)
                                    <select id="select_xa" class="form-control">
                                        <option value="all">--Chọn xã phường--</option>
                                        @foreach ($xas as $xa)
                                            <option {{ ($xa->maxa == $maxa) ? 'selected' : '' }} value="{{ $xa->maxa }}">{{ $xa->tenxa }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="portlet-body">
                        <div class="table-toolbar">

                        </div>
                    <table class="table table-striped table-bordered table-hover" id="sample_3">
                        <thead>
                        <tr>
                            <th style="text-align: center" width="2%">STT</th>
                            <th style="text-align: center" width="10%">Ảnh đại diện</th>
                            <th style="text-align: center;width: 20%" >Họ và tên</th>
                            <th style="text-align: center; width: 10% ">Ngày sinh</th>
                            <th style="text-align: center" width="5%">Giới tính</th>
                            <th style="text-align: center ; width: 10%">Hiện trạng</th>
                            <th style="text-align: center; width: 10%" >Ngày hưởng<br>Ngày dừng trợ cấp</th>
                            <th style="text-align: center ; width: 5%">Trạng thái</th>
                            <th style="text-align: center">Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($model as $key=>$tt)
                            <tr>
                                <td style="text-align: center">{{$key+1}}</td>
                                <td align="center" style="vertical-align: middle">
                                    <a href="{{url('danhsachdoituongtx/'.$tt->id)}}">
                                        <img src="{{ url('images/avatar/doituongtx/'.$tt->avatar)}}" width="96">
                                    </a>
                                </td>
                                <td class="active"><b style="color: blue">{{$tt->hoten}}</b><br><u>Mã hồ sơ:</u> {{$tt->mahoso}}</td>
                                <td style="text-align: center">{{getDayVn($tt->ngaysinh)}}</td>
                                <td style="text-align: center">{{$tt->gioitinh}}</td>
                                <td>{{$tt->trangthaihuong}}</td>
                                <td style="text-align: center">{{getDayVn($tt->ngayhuong)}} {{$tt->ngaydunghuong != '' ? '- '.getDayVn($tt->ngaydunghuong) : ''}}</td>
                                @if($tt->trangthaihoso == 'Đã duyệt')
                                    <td style="text-align: center"><span class="label label-sm label-success">Đã duyệt</span></td>
                                @elseif($tt->trangthaihoso == 'Chờ duyệt')
                                    <td style="text-align: center"><span class="label label-sm label-warning">Chờ duyệt</span></td>
                                @elseif($tt->trangthaihoso == 'Bị trả lại')
                                    <td style="text-align: center"><span class="label label-sm label-danger">Bị trả lại</span></td>
                                @else
                                    <td style="text-align: center"><span class="label label-sm label-info">Chờ chuyển </span></td>
                                @endif
                                <td>
                                    <a href="{{url('danhsachdoituongtx/'.$tt->id)}}" class="btn btn-default btn-xs mbs"><i class="fa fa-eye"></i>&nbsp;Xem chi tiết</a>
                                    @if(canEdit($tt->trangthaihoso))
                                        <a href="{{url('danhsachdoituongtx/'.$tt->id.'/edit')}}" class="btn btn-default btn-xs mbs"><i class="fa fa-edit"></i>&nbsp;Chỉnh sửa</a>

                                        @if($tt->trangthaihoso == 'Bị trả lại' || $tt->trangthaihoso == 'Chờ chuyển')
                                            @if(can('dttx','forward'))
                                            <button type="button" onclick="getIdChuyen('{{$tt->id}}}')" class="btn btn-default btn-xs mbs" data-target="#chuyen-modal" data-toggle="modal"><i class="fa fa-mail-forward"></i>&nbsp;
                                                Chuyển</button>
                                            @endif
                                            @if($tt->trangthaihoso == 'Bị trả lại')
                                                <button type="button" onclick="ShowLyDo('{{$tt->id}}')" class="btn btn-default btn-xs mbs" data-target="#lydo-modal" data-toggle="modal"><i class="fa fa-search"></i>&nbsp;
                                                    Lý do trả lại</button>
                                            @endif
                                        @endif
                                        @if($tt->trangthaihoso == 'Chờ duyệt')
                                            @if(can('dttx','approve'))
                                            <button type="button" onclick="getIdDuyet('{{$tt->id}}}')" class="btn btn-default btn-xs mbs" data-target="#duyet-modal" data-toggle="modal"><i class="fa fa-check"></i>&nbsp;
                                               Duyệt</button>
                                            <button type="button" onclick="getIdTraLai('{{$tt->id}}}')" class="btn btn-default btn-xs mbs" data-target="#tralai-modal" data-toggle="modal"><i class="fa fa-reply"></i>&nbsp;
                                                Trả lại</button>
                                            @endif
                                        @endif
                                        <button type="button" onclick="getId('{{$tt->id}}')" class="btn btn-default btn-xs mbs" data-target="#delete-modal" data-toggle="modal"><i class="fa fa-trash-o"></i>&nbsp;
                                            Xóa</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>

    <!-- BEGIN DASHBOARD STATS -->

    <!-- END DASHBOARD STATS -->

    </div>
    <div class="clearfix"></div>
    <div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['url'=>'danhsachdoituongtx/delete','id' => 'frm_delete'])!!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Đồng ý xóa?</h4>
                </div>
                <input type="hidden" name="iddelete" id="iddelete">
                <div class="modal-footer">
                    <button type="submit" class="btn blue" onclick="ClickDelete()">Đồng ý</button>
                    <button type="button" class="btn default" data-dismiss="modal">Hủy</button>
                </div>
                {!! Form::close() !!}
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="chuyen-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['url'=>'danhsachdoituongtx/chuyen','id' => 'frm_chuyen'])!!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Đồng ý chuyển hồ sơ?</h4>
                </div>
                <input type="hidden" name="idchuyen" id="idchuyen">
                <div class="modal-footer">
                    <button type="submit" class="btn blue" onclick="ClickChuyen()">Đồng ý</button>
                    <button type="button" class="btn default" data-dismiss="modal">Hủy</button>
                </div>
                {!! Form::close() !!}
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <div class="modal fade" id="duyet-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['url'=>'danhsachdoituongtx/duyet','id' => 'frm_duyet'])!!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Đồng ý duyệt hồ sơ?</h4>
                </div>
                <input type="hidden" name="idduyet" id="idduyet">
                <div class="modal-footer">
                    <button type="submit" class="btn blue" onclick="ClickDuyet()">Đồng ý</button>
                    <button type="button" class="btn default" data-dismiss="modal">Hủy</button>
                </div>
                {!! Form::close() !!}
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="tralai-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['url'=>'danhsachdoituongtx/tralai','id' => 'frm_tralai'])!!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Đồng ý trả lại?</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><b>Lý do trả lại</b></label>
                        <textarea id="lydotralai" class="form-control required" name="lydotralai" cols="30" rows="5"></textarea>
                    </div>
                </div>
                <input type="hidden" name="idtralai" id="idtralai">
                <div class="modal-footer">
                    <button type="submit" class="btn blue" onclick="ClickTraLai()">Đồng ý</button>
                    <button type="button" class="btn default" data-dismiss="modal">Hủy</button>
                </div>
                {!! Form::close() !!}
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="lydo-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Lý do trả lại hồ sơ!!!</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><b>Lý do trả lại</b></label>
                        <div id="lydo"></div>
                    </div>
                </div>
                <input type="hidden" name="idtralai" id="idtralai">
                <div class="modal-footer">
                    <button type="button" class="btn default" data-dismiss="modal">Hủy</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <script>
        $(function(){

            $('#select_trocap,#select_huyen,#select_xa').change(function() {
                var current_path_url = '/danhsachdoituongtx?';
                var trocap = '&trocap='+$('#select_trocap').val();
                if($(this).attr('id') == 'select_huyen'){
                    $('#select_xa').val('all');
                }
                var maxa = '';
                if($('#select_xa').length > 0 ){
                    var maxa = '&maxa='+$('#select_xa').val();
                }
                var mahuyen = '';
                if($('#select_huyen').length > 0 ){
                    var mahuyen = '&mahuyen='+$('#select_huyen').val();
                }
                var url = current_path_url+trocap+mahuyen+maxa;
                window.location.href = url;
            });
        })


    </script>

@stop