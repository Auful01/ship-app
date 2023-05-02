<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CountController extends Controller
{

    public function count(Request $request)
    {
        try {
            $dataRet = [];

            for ($i = 1; $i <= $request->number; $i++) {
                if ($this->isPrime($i)) {
                    array_push($dataRet, $i  . '=> FizBuzz');
                } else if ($this->isGanjil($i)) {
                    array_push($dataRet, $i  . '=> Fiz');
                } else if ($this->isGenap($i)) {
                    array_push($dataRet, $i  . '=> Buzz');
                }
            }
            return $dataRet;
        } catch (\Throwable $th) {
            //throw $th;

            return $th->getMessage();
        }
    }

    public function isGanjil($data)
    {
        if ($data % 2 != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function isGenap($data)
    {
        if ($data % 2 == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function isPrime($data)
    {
        if (($data == 2 || $data == 3 || $data == 5) || $data % 2 != 0 && $data % 3 != 0 && ($data != 1 && $data != 0) && $data % $data == 0 && $data % 5 != 0) {
            return true;
        } else {
            return false;
        }
    }
}
