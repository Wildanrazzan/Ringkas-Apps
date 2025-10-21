<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Dompet;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user = Auth::user();
        $transaksi = Transaksi::join('dompet', 'transaksi.dompet_id', '=', 'dompet.id')
            ->join('kategori', 'transaksi.category_id', '=', 'kategori.id')
            ->select('transaksi.*', 'dompet.name as dompet_name', 'kategori.name as kategori_name')
            ->where('dompet.user_id', $user->id)
            ->get();
        return new MessageResource($transaksi, '200', 'Data transaksi berhasil diambil');
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
            'dompet_id' => 'required|exists:dompet,id',
            'category_id' => 'required|exists:kategori,id',
            'trx_date' => 'required|date',
            'amount' => 'required|numeric',
            'note' => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }

        $dompet = Dompet::find($request->dompet_id);

        if(!$dompet){
            return response()->json(['Dompet tidak ditemukan'], 404);
        }

        if($dompet->user_id !== Auth::id()){
            return response()->json(['Tidak boleh memakai dompet yang bukan milik anda!'], 403);
        }

        $transaksi = Transaksi::create([
            'dompet_id' => $request->dompet_id,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'trx_date' => $request->trx_date,
            'note' => $request->note,
        ]);
        return new MessageResource($transaksi, '201', 'Transaksi berhasil dibuat');
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
            'dompet_id' => 'sometimes|exists:dompet,id',
            'category_id' => 'sometimes|exists:kategori,id',
            'trx_date' => 'sometimes|date',
            'amount' => 'sometimes|numeric',
            'note' => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }
        
        $transaksi = Transaksi::find($id);

        if(!$transaksi){
            return response()->json(['Transaksi tidak ditemukan'], 404);
        }

        $dompetLama = Dompet::find($transaksi->dompet_id);
        
        if(!$dompetLama || $dompetLama->user_id !== Auth::id()){
            return response()->json(['Tidak memiliki izin untuk mengupdate transaksi ini!'],403);
        }

        if($request->has('dompet_id')){
            $dompetBaru = Dompet::find($request->dompet_id);
            if(!$dompetBaru || $dompetBaru->user_id !== Auth::id()){
                return response()->json(['Dompet tersebut bukan milik anda!'], 403);
            }
        }

        $transaksi->update($request->only([
            'dompet_id',
            'category_id',
            'trx_date',
            'amount',
            'note',
        ]));

        return new MessageResource($transaksi, '200', 'Transaksi berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transaksi = Transaksi::find($id);

        if(!$transaksi){
            return response()->json(['Transaksi tidak ditemukan'], 404);
        }

        $dompet = Dompet::find($transaksi->dompet_id);
        
        if(!$dompet || $dompet->user_id !== Auth::id()){
            return response()->json(['Tidak memiliki izin untuk menghapus transaksi ini!'],403);
        }

        $transaksi->delete();
        return new MessageResource($transaksi, '200', 'Transaksi berhasil dihapus');
    }
}
