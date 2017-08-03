<?php

namespace App\Http\Controllers;

use App\Districts;
use App\DmTroCapTx;
use App\DsDoiTuongTx;
use App\PlTroCapTx;
use App\Towns;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DsDoiTuongTxController extends Controller
{
    public function index(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            if (session('admin')->level == 'T') {
                $huyendf = Districts::first()->mahuyen;
                $huyen = isset($inputs['mahuyen']) ? $inputs['mahuyen'] : $huyendf;
                $xadf = Towns:: where('mahuyen', $huyen)->first()->maxa;
                if (isset($inputs['maxa'])) {
                    if ($inputs['maxa'] == "all")
                        $xa = $xadf;
                    else
                        $xa = $inputs['maxa'];
                } else {
                    $xa = $xadf;
                }

            } elseif (session('admin')->level == 'H') {
                $huyen = isset($inputs['mahuyen']) ? $inputs['mahuyen'] : session('admin')->mahuyen;
                $xadf = Towns:: where('mahuyen', $huyen)->first()->maxa;
                $xa = isset($inputs['maxa']) ? $inputs['maxa'] : $xadf;
            } else {
                $huyen = isset($inputs['mahuyen']) ? $inputs['mahuyen'] : session('admin')->mahuyen;
                $xa = isset($inputs['maxa']) ? $inputs['maxa'] : session('admin')->maxa;
            }
            $trocap = isset($inputs['trocap']) ? $inputs['trocap'] : 'CD';

            $huyens = listHuyen();
            $xas = array();
            if ($huyen != 'all') {
                $xas = listXa($huyen);
            }
            $model = DsDoiTuongTx::where('pltrocap',$trocap);
            if($huyen != 'all' && $huyen != ''){
                $model = $model->where('mahuyen', $huyen);
            }
            if($xa != 'all' && $xa != ''){
                $model = $model->where('maxa', $xa);
            }else{
                $model = $model->where('maxa', $xa);
            }
            if (session('admin')->level == 'T' || session('admin')->level == 'H') {
                $model = $model->where('trangthaihoso', '<>', 'Chờ chuyển')
                    ->where('trangthaihoso', '<>', 'Bị trả lại');
            }
            $model = $model->get();


            return view('manage.danhsachdoituong.thuongxuyen.index')
                ->with('huyens', $huyens)
                ->with('xas', $xas)
                ->with('mahuyen', $huyen)
                ->with('maxa', $xa)
                ->with('trocap', $trocap)
                ->with('model',$model)
                ->with('pageTitle', 'Danh sách đối tượng trợ cấp thường xuyên');
        }else
            return view('errors.notlogin');
    }

    public function create($trocap)
    {
        if (Session::has('admin')) {
            if (session('admin')->level == 'T') {
                $huyens = Districts::all();
                $huyendf = Districts::first()->mahuyen;
                $xas = Towns::where('mahuyen', $huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            } elseif (session('admin')->level == 'H') {
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen', session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen', $huyendf)
                    ->get();
                $xadf = $xas->first()->maxa;
            } else {
                $huyens = Districts::all();
                $huyendf = Districts::where('mahuyen', session('admin')->mahuyen)->first()->mahuyen;
                $xas = Towns::where('mahuyen', $huyendf)
                    ->get();
                $xadf = $xas->where('maxa', session('admin')->maxa)->first()->maxa;
            }
            $selectloaidt = DmTroCapTx::where('pltrocap', $trocap)->get();
            $selectnoidungtc = $this->getNoiDungTcTx($trocap);
            $selectchitiettc = $this->getChiTietTcTx($selectnoidungtc);

            $loaitrocapdf = $selectloaidt->first()->matrocap;
            $loaitrocap = PlTroCapTx::where('maloai', $trocap)->first();
            return view('manage.danhsachdoituong.thuongxuyen.create')
                ->with('action', 'create')
                ->with('huyens', $huyens)
                ->with('mahuyen', $huyendf)
                ->with('xas', $xas)
                ->with('maxa', $xadf)
                ->with('trocap', $trocap)
                ->with('loaitrocap', $loaitrocap)
                ->with('selectloaidt', $selectloaidt)
                ->with('selectnoidungtc',$selectnoidungtc)
                ->with('selectchitiettc',$selectchitiettc)
                ->with('loaitrocapdf',$loaitrocapdf)
                ->with('trocap',$trocap)
                ->with('pageTitle', 'Thêm mới đối tượng trợ cấp thường xuyên');
        } else
            return view('errors.notlogin');
    }

    public function getNoiDungTcTx($trocap){
        $model = DmTroCapTx::where('pltrocap',$trocap)->groupby('noidung')->select('noidung')->geT();
        $options = array();

        foreach ($model as $tt) {

            $options[] = $tt->noidung;
        }
        return $options;
    }

    public function getChiTietTcTx($noidung){
        $model = DmTroCapTx::where('noidung',$noidung)->get();
        $options = array();

        foreach ($model as $tt) {

            $options[$tt->matrocap] = $tt->chitiet.'- Hệ số: '.$tt->heso;
        }
        return $options;
    }

    public function store(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $inputs['matinh'] = getmatinh();
            $inputs['ttthaotac'] = session('admin')->name .'('.session('admin')->username.')'.'- Thêm mới';
            $inputs['mahoso'] = getmatinh().$inputs['mahuyen'].$inputs['maxa'].'TX'.$this->getIdForCreateBTXH();
            $inputs['ngaysinh'] = getDateToDb($inputs['ngaysinh']);
            $inputs['ngayhuong'] = getDateToDb($inputs['ngayhuong']);
            $inputs['ngaydunghuong'] = getDateToDb($inputs['ngaydunghuong']);
            $inputs['sotientc'] = getMoneyToDb($inputs['sotientc']);

            if(session('admin')->level == 'T')
                $inputs['trangthaihoso'] = 'Đã duyệt';
            else
                $inputs['trangthaihoso'] = 'Chờ chuyển';
            //UpLoadAvatar
            if(isset($inputs['avatar'])){
                $inputs['avatar'] = 'no-image.png';
            }else{
                $avatar = $request->file('avatar');
                $inputs['avatar'] = $inputs['mahoso'] .'.'.$avatar->getClientOriginalExtension();
                $avatar->move(public_path() . '/images/avatar/doituongtx/', $inputs['avatar']);
            }
            //EndUpLoadAvatar
            //FileUpLoad
            if(isset($inputs['ipf1'])){
                $ipf1 = $request->file('ipf1');
                $inputs['ipf1'] = $inputs['mahoso'].'_1_'.changeNameFile($ipf1->getClientOriginalName());
                $ipf1->move(public_path().'/file/doituongtx/',$inputs['ipf1']);
            }
            if(isset($inputs['ipf2'])){
                $ipf2 = $request->file('ipf2');
                $inputs['ipf2'] = $inputs['mahoso'].'_2_'.changeNameFile($ipf2->getClientOriginalName());
                $ipf2->move(public_path().'/file/doituongtx/',$inputs['ipf2']);
            }
            if(isset($inputs['ipf3'])){
                $ipf3 = $request->file('ipf3');
                $inputs['ipf3'] = $inputs['mahoso'].'_3_'.changeNameFile($ipf3->getClientOriginalName());
                $ipf3->move(public_path().'/file/doituongtx/',$inputs['ipf3']);
            }
            if(isset($inputs['ipf4'])){
                $ipf4 = $request->file('ipf4');
                $inputs['ipf4'] = $inputs['mahoso'].'_4_'.changeNameFile($ipf4->getClientOriginalName());
                $ipf4->move(public_path().'/file/doituongtx/',$inputs['ipf4']);
            }
            if(isset($inputs['ipf5'])){
                $ipf5 = $request->file('ipf5');
                $inputs['ipf5'] = $inputs['mahoso'].'_5_'.changeNameFile($ipf5->getClientOriginalName());
                $ipf5->move(public_path().'/file/doituongtx/',$inputs['ipf5']);
            }
            if(isset($inputs['ipf6'])){
                $ipf6 = $request->file('ipf6');
                $inputs['ipf6'] = $inputs['mahoso'].'_6_'.changeNameFile($ipf6->getClientOriginalName());
                $ipf6->move(public_path().'/file/doituongtx/',$inputs['ipf6']);
            }
            if(isset($inputs['ipf7'])){
                $ipf7 = $request->file('ipf7');
                $inputs['ipf7'] = $inputs['mahoso'].'_7_'.changeNameFile($ipf7->getClientOriginalName());
                $ipf7->move(public_path().'/file/doituongtx/',$inputs['ipf7']);
            }
            if(isset($inputs['ipf8'])){
                $ipf8 = $request->file('ipf8');
                $inputs['ipf8'] = $inputs['mahoso'].'_8_'.changeNameFile($ipf8->getClientOriginalName());
                $ipf8->move(public_path().'/file/doituongtx/',$inputs['ipf8']);
            }
            if(isset($inputs['ipf9'])){
                $ipf9 = $request->file('ipf9');
                $inputs['ipf9'] = $inputs['mahoso'].'_9_'.changeNameFile($ipf9->getClientOriginalName());
                $ipf9->move(public_path().'/file/doituongtx/',$inputs['ipf9']);
            }
            if(isset($inputs['ipf10'])){
                $ipf10 = $request->file('ipf10');
                $inputs['ipf10'] = $inputs['mahoso'].'_10_'.changeNameFile($ipf10->getClientOriginalName());
                $ipf10->move(public_path().'/file/doituongtx/',$inputs['ipf10']);
            }
            //EndFileUpLoad

            $model = new DsDoiTuongTx();
            $model->create($inputs);
            return redirect('danhsachdoituongtx?&trocap='.$inputs['pltrocap']);
        } else
            return view('errors.notlogin');
    }

    public function getIdForCreateBTXH() {

        $maxid = DsDoiTuongTx::max('id');

        return $maxid + 1;
    }

    public function edit($id){
        if (Session::has('admin')) {
            $model = DsDoiTuongTx::find($id);
            $huyens = Districts::all();
            $xas = Towns::where('mahuyen', $model->mahuyen)->get();
            $huyendf = $model->mahuyen;
            $xadf = $model->maxa;
            $selectnoidungtc = $this->getNoiDungTcTx($model->pltrocap);
            $selectchitiettc = $this->getChiTietTcTx($selectnoidungtc);

            $tttrocap = DmTroCapTx::where('matrocap',$model->matrocap)->first();
            $loaitrocap = PlTroCapTx::where('maloai', $model->pltrocap)->first();
            return view('manage.danhsachdoituong.thuongxuyen.edit')
                ->with('action', 'edit')
                ->with('huyens', $huyens)
                ->with('mahuyen', $huyendf)
                ->with('xas', $xas)
                ->with('maxa', $xadf)
                ->with('selectnoidungtc',$selectnoidungtc)
                ->with('selectchitiettc',$selectchitiettc)
                ->with('loaitrocap',$loaitrocap)
                ->with('model',$model)
                ->with('tttrocap',$tttrocap)
                ->with('pageTitle', 'Chỉnh sửa đối tượng trợ cấp thường xuyên');
        } else
            return view('errors.notlogin');
    }

    public function update(Request $request,$id){
        if (Session::has('admin')) {
            $inputs = $request->all();

            $inputs['matinh'] = getmatinh();
            $inputs['ttthaotac'] = session('admin')->name .'('.session('admin')->username.')'.'- Cập nhật';
            $inputs['ngaysinh'] = getDateToDb($inputs['ngaysinh']);
            $inputs['ngayhuong'] = getDateToDb($inputs['ngayhuong']);
            $inputs['ngaydunghuong'] = getDateToDb($inputs['ngaydunghuong']);
            $inputs['sotientc'] = getMoneyToDb($inputs['sotientc']);
            $model = DsDoiTuongTx::find($id);
            //UpAvatar
            if(isset($inputs['avatar'])){
                $avatar = $request->file('avatar');
                $inputs['avatar'] = $model->mahoso .'.'.$avatar->getClientOriginalExtension();
                $avatar->move(public_path() . '/images/avatar/doituongtx/', $inputs['avatar']);
            }
            //End UpAvatar
            //FileUpLoad
            if(isset($inputs['ipf1'])){
                $ipf1 = $request->file('ipf1');
                $inputs['ipf1'] = $model->mahoso.'_1_'.changeNameFile($ipf1->getClientOriginalName());
                $ipf1->move(public_path().'/file/doituongtx/',$inputs['ipf1']);
            }
            if(isset($inputs['ipf2'])){
                $ipf2 = $request->file('ipf2');
                $inputs['ipf2'] = $model->mahoso.'_2_'.changeNameFile($ipf2->getClientOriginalName());
                $ipf2->move(public_path().'/file/doituongtx/',$inputs['ipf2']);
            }
            if(isset($inputs['ipf3'])){
                $ipf3 = $request->file('ipf3');
                $inputs['ipf3'] = $model->mahoso.'_3_'.changeNameFile($ipf3->getClientOriginalName());
                $ipf3->move(public_path().'/file/doituongtx/',$inputs['ipf3']);
            }
            if(isset($inputs['ipf4'])){
                $ipf4 = $request->file('ipf4');
                $inputs['ipf4'] = $model->mahoso.'_4_'.changeNameFile($ipf4->getClientOriginalName());
                $ipf4->move(public_path().'/file/doituongtx/',$inputs['ipf4']);
            }
            if(isset($inputs['ipf5'])){
                $ipf5 = $request->file('ipf5');
                $inputs['ipf5'] = $model->mahoso.'_5_'.changeNameFile($ipf5->getClientOriginalName());
                $ipf5->move(public_path().'/file/doituongtx/',$inputs['ipf5']);
            }
            if(isset($inputs['ipf6'])){
                $ipf6 = $request->file('ipf6');
                $inputs['ipf6'] = $model->mahoso.'_6_'.changeNameFile($ipf6->getClientOriginalName());
                $ipf6->move(public_path().'/file/doituongtx/',$inputs['ipf6']);
            }
            if(isset($inputs['ipf7'])){
                $ipf7 = $request->file('ipf7');
                $inputs['ipf7'] = $model->mahoso.'_7_'.changeNameFile($ipf7->getClientOriginalName());
                $ipf7->move(public_path().'/file/doituongtx/',$inputs['ipf7']);
            }
            if(isset($inputs['ipf8'])){
                $ipf8 = $request->file('ipf8');
                $inputs['ipf8'] = $model->mahoso.'_8_'.changeNameFile($ipf8->getClientOriginalName());
                $ipf8->move(public_path().'/file/doituongtx/',$inputs['ipf8']);
            }
            if(isset($inputs['ipf9'])){
                $ipf9 = $request->file('ipf9');
                $inputs['ipf9'] = $model->mahoso.'_9_'.changeNameFile($ipf9->getClientOriginalName());
                $ipf9->move(public_path().'/file/doituongtx/',$inputs['ipf9']);
            }
            if(isset($inputs['ipf10'])){
                $ipf10 = $request->file('ipf10');
                $inputs['ipf10'] = $model->mahoso.'_10_'.changeNameFile($ipf10->getClientOriginalName());
                $ipf10->move(public_path().'/file/doituongtx/',$inputs['ipf10']);
            }
            //EndFileUpLoad

            $model->update($inputs);
            return redirect('danhsachdoituongtx?&trocap='.$inputs['pltrocap']);
        } else
            return view('errors.notlogin');
    }

    public function show($id){
        if (Session::has('admin')) {
            $model = DsDoiTuongTx::find($id);
            $huyen = Districts::where('mahuyen',$model->mahuyen)->first()->tenhuyen;
            $xa = Towns::where('maxa',$model->maxa)->first()->tenxa;
            $loaitc = DmTroCapTx::where('matrocap',$model->matrocap)->first();
            $loaidt = PlTroCapTx::where('maloai',$model->pltrocap)->first()->tenloai;

            return view('manage.danhsachdoituong.thuongxuyen.show')
                ->with('model',$model)
                ->with('dvql',$xa.'- '.$huyen)
                ->with('loaitc',$loaitc)
                ->with('loaidt',$loaidt)
                ->with('attachments',$this->getAttachments($model))
                ->with('pageTitle', 'Thông tin đối tượng trợ cấp thường xuyên');
        } else
            return view('errors.notlogin');
    }

    public function destroy(Request $request){
        if (Session::has('admin')) {
            $id = $request->all()['iddelete'];
            $model = DsDoiTuongTx::find($id);
            $pltrocap = $model->pltrocap;
            $model->delete();
            return redirect('danhsachdoituongtx?&trocap='.$pltrocap);
        } else
            return view('errors.notlogin');
    }

    public function tralai(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idtralai'];
            $model = DsDoiTuongTx::find($id);
            $model->trangthaihoso = 'Bị trả lại';
            $model->lydotralai = $inputs['lydotralai'];
            $model->save();
            $pltrocap = $model->pltrocap;
            return redirect('danhsachdoituongtx?&trocap='.$pltrocap);
        } else
            return view('errors.notlogin');
    }

    public function chuyen(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idchuyen'];
            $model = DsDoiTuongTx::find($id);
            $model->trangthaihoso = 'Chờ duyệt';
            $model->save();
            $pltrocap = $model->pltrocap;
            return redirect('danhsachdoituongtx?&trocap='.$pltrocap);
        } else
            return view('errors.notlogin');
    }

    public function duyet(Request $request){
        if (Session::has('admin')) {
            $inputs = $request->all();
            $id = $inputs['idduyet'];
            $model = DsDoiTuongTx::find($id);
            $model->trangthaihoso = 'Đã duyệt';
            $model->save();
            $pltrocap = $model->pltrocap;
            return redirect('danhsachdoituongtx?&trocap='.$pltrocap);
        } else
            return view('errors.notlogin');
    }

    public function lydo(Request $request){
        $result = array(
            'status' => 'fail',
            'message' => 'error',
        );
        if(!Session::has('admin')) {
            $result = array(
                'status' => 'fail',
                'message' => 'permission denied',
            );
            die(json_encode($result));
        }
        //dd($request);
        $inputs = $request->all();

        if(isset($inputs['id'])){
            $model = DsDoiTuongTx::where('id',$inputs['id'])
                ->first();
            $result['message'] = '<div id="lydo" style="color: blue">'.$model->lydotralai.'</div>';
            $result['status'] = 'success';
        }
        die(json_encode($result));
    }

    public function getAttachments($model) {

        $attachments = array();

        $attachment = array();

        for ($i = 1; $i <= 10; $i++) {
            $ipf = 'ipf' . $i;
            if ($model->$ipf != null) {
                $ipt = 'ipt' . $i;
                $attachment['ipt'] = $model->$ipt;
                $attachment['ipf'] = $model->$ipf;
                $attachments[] = $attachment;
            }
        }

        return $attachments;
    }



}
