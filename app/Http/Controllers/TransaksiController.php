<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Http\Resources\MassageResource;
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
        return new MassageResource($transaksi, '200', 'Data transaksi berhasil diambil');
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
        $transaksi = Transaksi::create([
            'dompet_id' => $request->dompet_id,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'trx_date' => $request->trx_date,
            'note' => $request->note,
        ]);
        return new MassageResource($transaksi, '201', 'Transaksi berhasil dibuat');
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
            'dompet_id' => 'required|exists:dompet,id',
            'category_id' => 'required|exists:kategori,id',
            'trx_date' => 'required|date',
            'amount' => 'required|numeric',
            'note' => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }
        Transaksi::whereId($id)->update([
            'dompet_id' => $request->dompet_id,
            'category_id' => $request->category_id,
            'trx_date' => $request->trx_date,
            'amount' => $request->amount,
            'note' => $request->note,
        ]);
        $transaksi = Transaksi::find($id);
        return new MassageResource($transaksi, '200', 'Transaksi berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $transaksi = Transaksi::whereId($id)->first();
        $transaksi->delete();
        return new MassageResource($transaksi, '204', 'Transaksi berhasil dihapus');
    }
}
