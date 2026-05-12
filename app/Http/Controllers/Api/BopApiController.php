<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BopService;
use Illuminate\Http\Request;

class BopApiController extends Controller
{
    /**
     * Update BOP actual amount
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAktual(Request $request)
    {
        $request->validate([
            'kode_akun' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'type' => 'sometimes|in:add,subtract,set'
        ]);

        $kodeAkun = $request->kode_akun;
        $amount = (float) $request->amount;
        $type = $request->type ?? 'add';

        if ($type === 'set') {
            $success = BopService::recalculateAktual($kodeAkun, $amount);
        } else {
            $success = BopService::updateAktual($kodeAkun, $amount, $type);
        }

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'BOP actual updated successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update BOP actual'
        ], 500);
    }
}
