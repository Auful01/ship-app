<?php

namespace App\Http\Controllers;

use App\Models\Ship;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class ShipController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            if ($request->segment(2) == 'ship-pub') {
                $data = Ship::all(['id', 'nama_kapal', 'foto_kapal', 'nama_pemilik', 'nomor_izin', 'status']);
            } else {
                $data = Ship::all();
            }
            return $this->success($data, 'Data Ship');
        } catch (\Throwable $th) {
            //throw $th;
            return $this->error($th->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $filename_kapal = '';
            $filename_dokumen = '';
            if ($request->file('foto_kapal')) {
                $file_kapal = $request->file('foto_kapal');
                $filename_kapal = time() . '_' . $file_kapal->getClientOriginalName();
                $request->file('foto_kapal')->storeAs('foto_kapal', $filename_kapal, 'public');
            }

            if ($request->file('dokumen_perizinan')) {
                $file_dokumen = $request->file('dokumen_perizinan');
                $filename_dokumen = time() . '_' . $file_dokumen->getClientOriginalName();
                $request->file('dokumen_perizinan')->storeAs('dokumen_perizinan', $filename_dokumen, 'public');
            }


            $request['user_id'] = Auth::user()->id;
            $request['nomor_izin'] = "SHIP-" . rand(100000, 999999);

            $data = Ship::create($request->all());
            $data->update([
                "foto_kapal" => $filename_kapal,
                "dokumen_perizinan" => $filename_dokumen
            ]);

            DB::commit();
            return $this->success($data, 'Data Ship Created');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return $this->error($th->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {

            DB::beginTransaction();
            $data = Ship::findOrFail($id);
            if ($request->update == "verify") {
                $data->update([
                    "status" => $request->verify
                ]);
            } else {
                $data->update($request->all());
            }

            DB::commit();
            return $this->success($data, 'Data Ship Updated');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return $this->error($th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $data = Ship::findOrFail($id);
            $data->delete();

            DB::commit();
            return $this->success($data, 'Data Ship Deleted');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return $this->error($th->getMessage());
        }
    }
}
