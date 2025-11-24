<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dompet;
use App\Models\Transaksi;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DompetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user = Auth::user();
        $dompets = Dompet::join('users', 'dompet.user_id', '=', 'users.id')
            ->select('dompet.*', 'users.name as user_name')
            ->where('dompet.user_id', $user->id)
            ->get();

        // Tambahkan current_balance ke setiap dompet dan hitung total
        $totalCurrentBalance = 0;
        $dompets = $dompets->map(function ($dompet) use (&$totalCurrentBalance) {
            $dompet->current_balance = $this->getCurrentBalance($dompet->id);
            $totalCurrentBalance += $dompet->current_balance;
            return $dompet;
        });

        return new MessageResource([
            'dompets' => $dompets,
            'total_current_balance' => $totalCurrentBalance
        ], '200', 'Data dompet berhasil diambil');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'initial_balance' => 'required|decimal:0,2',
            'is_active' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }
        $dompet = Dompet::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'currency' => $request->currency,
            'initial_balance' => $request->initial_balance,
            'is_active' => $request->is_active,
        ]);
        return new MessageResource($dompet, '201', 'Dompet berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'currency' => 'sometimes|required|string|max:10',
            'initial_balance' => 'sometimes|required|decimal:0,2',
            'is_active' => 'sometimes|required|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }
        
        $dompet = Dompet::find($id);

        if(!$dompet){
            return response()->json(['Dompet tidak ditemukan'], 404);
        }

        if($dompet->user_id !== Auth::id()){
            return response()->json(['Tidak boleh update dompet yang bukan milik anda!'], 403);
        }

        $dompet->update($request->only([
            'name',
            'currency',
            'initial_balance',
            'is_active'
        ]));

        return new MessageResource($dompet, '200', 'Dompet berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $dompet = Dompet::find($id);

        if(!$dompet){
            return response()->json(['Dompet tidak ditemukan'], 404);
        }

        if($dompet->user_id !== Auth::id()){
            return response()->json(['Tidak boleh update dompet yang bukan milik anda!'], 403);
        }

        $dompet->delete();
        return new MessageResource($dompet, '200', 'Dompet berhasil dihapus');
    }

    /**
     * Helper: Hitung current balance untuk sebuah dompet
     * current_balance = initial_balance + sum(semua transaksi)
     */
    private function getCurrentBalance($dompet_id)
    {
        $dompet = Dompet::find($dompet_id);
        
        if (!$dompet) {
            return 0;
        }

        // Hitung total semua transaksi untuk dompet ini
        $totalTransaksi = Transaksi::where('dompet_id', $dompet_id)->sum('amount');

        // Current balance = initial_balance + total transaksi
        return $dompet->initial_balance + $totalTransaksi;
    }
}
