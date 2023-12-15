<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\DataFeed;
use Carbon\Carbon;
use App\Models\Users;
use App\Models\Sensor;
use App\Models\Lahan;
use App\Models\User;

class UserController extends Controller
{
    public function index(){

        $level=Auth::user()->level;

        if($level=='admin'){
            $batas = 15;
            $users = User::with(['lahan.sensor'])
            ->orderBy('id', 'desc')
            ->paginate($batas);

            $lahan= Lahan::orderBy('id_lahan', 'desc')->paginate($batas);
            $sensor = Sensor::orderBy('id_sensor', 'desc')->paginate($batas);
            $jumlah_users = User::count();
            $jumlah_sensor = Sensor::count();
            $jumlah_lahan = Lahan::count();
            $no=$batas*($users->currentPage() - 1);


            return view('pages/dashboard/dashboard', compact('users', 'sensor','lahan','jumlah_users', 'jumlah_lahan', 'jumlah_sensor'));
        }
        else{
            return view('/user/user-dashboard');
        }
    }

    //DAFTAR TABLE
    public function daftar_lahan()
    {
        $dataFeed = new DataFeed();
        $batas = 15;
        $lahan= Lahan::orderBy('id_lahan', 'desc')->paginate($batas);
        $no = $batas * ($lahan->currentPage() - 1);
        return view('pages/add/daftar-lahan', compact('dataFeed','lahan'));
    }
   
    public function daftar_farmer()
    {
        $dataFeed = new DataFeed();
        $batas = 15;
        $users = User::with(['lahan.sensor'])
        ->orderBy('id', 'desc')
        ->paginate($batas);
        $no=$batas*($users->currentPage() - 1);

        return view('pages/add/daftar-farmer', compact('dataFeed', 'users'));
    }

    public function daftar_sensor()
    {
        $dataFeed = new DataFeed();
        $batas = 15;
        $sensor = Sensor::orderBy('id_sensor', 'desc')->paginate($batas);
        $no = $batas * ($sensor->currentPage() - 1);
        return view('pages/add/daftar-sensor', compact('dataFeed','sensor'));
    }




    //SEARCH//
    public function search_farmer(Request $request) {
        $batas = 5;
        $search = $request->search;
        $user = User::where('id', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->paginate($batas);
        $no = $batas * ($user->currentPage() - 1);
        return view('pages/search/search-farmer', compact('search', 'user', 'no'));
    }

    public function search_lahan(Request $request) {
        $batas = 5;
        $search = $request->search;
        $lahan = Lahan::where('id_lahan', 'like', "%$search%")
                      ->orWhere('id_user', 'like', "%$search%")
                      ->paginate($batas);
        $no = $batas * ($lahan->currentPage() - 1);
        return view('pages/search/search-lahan', compact('search', 'lahan', 'no'));
    }

    public function search_sensor(Request $request) {
        $batas = 5;
        $search = $request->search;
        $sensor = Sensor::where('id_sensor', 'like', "%$search%")
                      ->orWhere('id_lahan', 'like', "%$search%")
                      ->paginate($batas);
    
        $no = $batas * ($sensor->currentPage() - 1);
        return view('pages/search/search-sensor', compact('search', 'sensor', 'no'));
    }




    //STORE//
    public function store_farmer(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'alamat_user' => 'required'
        ]);
        $batas = 15;
        $user= User::orderBy('id', 'desc')->paginate($batas);
        User::create([
            'name' =>$request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'alamat_user' => $request->alamat_user
            
        ]);
        return redirect('/pages/add/daftar-farmer')->with('pesan', 'Data farmer berhasil ditambahkan');
    }

    public function store_lahan(Request $request)
    {
        $batas = 15;
        $lahan = Lahan::orderBy('id_lahan', 'desc')->paginate($batas);

        $request->validate([
            'id_user' => 'required|exists:users,id',
            'alamat_lahan' => 'required',
            'luas_lahan' => 'required|numeric',
            'id_lahan' => 'required|unique:lahan,id_lahan',
        ], [
            'id_user.exists' => 'ID User tidak tersedia dalam database.',
            'alamat_lahan.required' => 'Alamat lahan harus di isi.',
            'id_lahan.required' => 'ID Lahan harus diisi.',
            'id_lahan.unique' => 'ID Lahan sudah digunakan.',
            'luas_lahan.required' => 'Luas lahan harus diisi.',
            'luas_lahan.numeric' => 'Luas lahan harus berupa nilai numerik.',
        ]);

        Lahan::create([
            'id_lahan' => $request->id_lahan,
            'id_user' => $request->id_user,
            'alamat_lahan' => $request->alamat_lahan,
            'luas_lahan' => $request->luas_lahan,
        ]);

        return redirect('/pages/add/daftar-lahan')->with('pesan', 'Data lahan berhasil ditambahkan');
    }

    public function store_sensor(Request $request)
    {
        $request->validate([
            'id_lahan' => 'required',
            'tanggal_aktivasi' => 'required|date_format:Y-m-d H:i:s'
        ]);
        $batas = 15;
        $sensor= Sensor::orderBy('id_sensor', 'desc')->paginate($batas);
        Sensor::create([
            'id_lahan' =>$request->id_lahan,
            'tanggal_aktivasi' => $request->tanggal_aktivasi
        ]);
        return redirect('/pages/add/daftar-sensor')->with('pesan', 'Data farmer berhasil ditambahkan');
    }




    //EDIT//
    public function form_farmer_edit(string $id) {
        $users = User::find($id);
        $sensor = Lahan::with('sensor')->where('id_user', $id)->get();
        // dd($sensor);
        return view('pages.edit-delete.read-farmer', compact('users', 'sensor'));        }
    public function form_lahan_edit(string $id_lahan) {
        $lahan = Lahan::find($id_lahan);
        return view('pages.edit-delete.form-lahan', compact('lahan'));
    }
    public function form_sensor_edit(string $id_sensor) {
        $sensor = Sensor::find($id_sensor);
        return view('pages.edit-delete.form-sensor', compact('sensor'));
    }




    //READ//
   
    public function read_farmer_edit($id) {
        $users = User::find($id);
        // $sensor = $users->lahan->sensor;
        // $sensor = Sensor::with(['la'])
        // $sensor = $users->with('lahan.sensor')->get();
        $sensor = Lahan::with('sensor')->where('id_user', $id)->get();
        // dd($sensor);
        return view('pages.edit-delete.read-farmer', compact('users', 'sensor'));
    }
    
    
    public function read_lahan_edit(string $id_lahan) {
        $lahan = Lahan::find($id_lahan);
        return view('pages.edit-delete.read-lahan', compact('lahan'));
    }
    public function read_sensor_edit(string $id_sensor) {
        $sensor = Sensor::find($id_sensor);
        return view('pages.edit-delete.read-sensor', compact('sensor'));
    }

    public function read_auth_edit(string $id) {
        $users = User::find($id);
        return view('pages.edit-delete.read-auth', compact('users'));
    }




    //DESTROY//
    public function form_farmer_destroy($id){
        $users = User::find($id);
        $users->delete();
        return redirect('/pages/add/daftar-farmer');
    }

    public function form_lahan_destroy($id_lahan){
        $lahan = Lahan::find($id_lahan);
        $lahan->delete();
        return redirect('/pages/add/daftar-lahan');
    }
    public function form_sensor_destroy($id_sensor){
        $sensor = Sensor::find($id_sensor);
        $sensor->delete();
        return redirect('/pages/add/daftar-sensor');
    }





    //UPDATE//
    public function form_farmer_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required|min:8',
            'alamat_user' => 'required'
        ]);
        
        $users = User::find($id);
        $users->name = $request->input('name');
        $users->email = $request->input('email');
        $users->password = bcrypt($request->input('password'));
        $users->alamat_user = $request->input('alamat_user');
        $users->save();

    return redirect('/pages/add/daftar-farmer')->with('success', 'Farmer updated successfully');
    }

    public function form_lahan_update(Request $request, $id_lahan)
    {
        $request->validate([
            'id_lahan' => 'required|string', 
            'id_user' => 'required|string', 
            'luas_lahan' => 'required|numeric',
            'alamat_lahan' => 'required|string',
        ]);

        $lahan = Lahan::find($id_lahan);
        $lahan->id_lahan = $request->input('id_lahan');
        $lahan->id_user = $request->input('id_user');
        $lahan->luas_lahan = $request->input('luas_lahan');
        $lahan->alamat_lahan = $request->input('alamat_lahan');
        $lahan->save();

        return redirect('/pages/add/daftar-lahan')->with('success', 'Lahan updated successfully');
    }


    public function form_sensor_update(Request $request, $id_sensor)
    {
        $request->validate([
            'id_sensor' => 'required|string',
            'id_lahan' => 'required|string',
            'tanggal_aktivasi' => 'required|string',
    ]);
        $sensor = Sensor::find($id_sensor);
        $sensor->id_sensor= $request->input('id_sensor');
        $sensor->id_lahan = $request->input('id_lahan');
        $sensor->tanggal_aktivasi = $request->input('tanggal_aktivasi');
        $sensor->save();
    return redirect('/pages/add/daftar-sensor')->with('success', 'Sensor updated successfully');
    }
    

    public function form_auth_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required|min:8',
            'alamat_user' => 'required'
        ]);
        $users = User::find($id);
        $users->name = $request->input('name');
        $users->email = $request->input('email');
        $users->password = bcrypt($request->input('password'));
        $users->alamat_user = $request->input('alamat_user');
        $users->save();
    return redirect('/pages/dashboard/dashboard')->with('success', 'Admin updated successfully');
    }
}
