<?php

namespace App\Http\Controllers;

use App\Http\Traits\Response;
use App\Models\Ship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipController extends Controller
{
    use Response;

    public function index()
    {
        $data = Ship::all();
        return $this->success($data, 'Data Ship');
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = Ship::create($request->all());

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
