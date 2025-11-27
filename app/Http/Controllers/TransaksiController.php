<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Dompet;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $user = Auth::user();
        
        $query = Transaksi::join('dompet', 'transaksi.dompet_id', '=', 'dompet.id')
            ->join('kategori', 'transaksi.category_id', '=', 'kategori.id')
            ->select('transaksi.*', 'dompet.name as dompet_name', 'kategori.name as kategori_name')
            ->where('dompet.user_id', $user->id);

        $start_date = null;
        $end_date = null;

        // Filter berdasarkan tanggal mulai
        if ($request->has('start_date') && $request->start_date) {
            $start_date = $request->start_date;
            $query->whereDate('transaksi.trx_date', '>=', $start_date);
        }

        // Filter berdasarkan tanggal akhir
        if ($request->has('end_date') && $request->end_date) {
            $end_date = $request->end_date;
            $query->whereDate('transaksi.trx_date', '<=', $end_date);
        }

        // Filter berdasarkan search keyword (pada note/deskripsi)
        if ($request->has('search') && $request->search) {
            $query->where('transaksi.note', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan wallet/dompet (support multiple values: dompet_id=1,2,3)
        if ($request->has('dompet_id') && $request->dompet_id) {
            $dompet_ids = explode(',', $request->dompet_id);
            $dompet_ids = array_map('trim', $dompet_ids);
            $query->whereIn('transaksi.dompet_id', $dompet_ids);
        }

        // Filter berdasarkan kategori (support multiple values: category_id=1,2,3)
        if ($request->has('category_id') && $request->category_id) {
            $category_ids = explode(',', $request->category_id);
            $category_ids = array_map('trim', $category_ids);
            $query->whereIn('transaksi.category_id', $category_ids);
        }

        // Urutkan berdasarkan tanggal terbaru
        $transaksi = $query->orderBy('transaksi.trx_date', 'desc')->get();

        // Hitung saldo awal dan saldo akhir berdasarkan tanggal (total semua dompet)
        $balances = $this->calculateBalances($user->id, $start_date, $end_date);

        return new MessageResource([
            'transaksi' => $transaksi,
            'saldo_awal' => $balances['saldo_awal'],
            'saldo_akhir' => $balances['saldo_akhir'],
            'total_transaksi' => $balances['total_transaksi']
        ], '200', 'Data transaksi berhasil diambil');
    }

    /**
     * Hitung saldo awal dan saldo akhir berdasarkan tanggal (total semua dompet)
     */
    private function calculateBalances($user_id, $start_date = null, $end_date = null)
    {
        // Ambil semua dompet aktif user
        $dompets = Dompet::where('user_id', $user_id)
            ->where('is_active', true)
            ->get();

        $saldo_awal_total = 0;
        $saldo_akhir_total = 0;
        $total_transaksi = 0;

        foreach ($dompets as $dompet) {
            // ===== 1. HITUNG TRANSAKSI SEBELUM START_DATE =====
            $before_query = Transaksi::where('transaksi.dompet_id', $dompet->id);

            if ($start_date) {
                $before_query->whereDate('transaksi.trx_date', '<', $start_date);
            }

            // Amount sudah termasuk tanda (+ untuk income, - untuk expense)
            $perubahan_before = $before_query->sum('transaksi.amount');

            // ===== 2. HITUNG TRANSAKSI DALAM RANGE =====
            $range_query = Transaksi::where('transaksi.dompet_id', $dompet->id);

            if ($start_date) {
                $range_query->whereDate('transaksi.trx_date', '>=', $start_date);
            }

            if ($end_date) {
                $range_query->whereDate('transaksi.trx_date', '<=', $end_date);
            }

            // Amount sudah termasuk tanda (+ untuk income, - untuk expense)
            $perubahan_range = $range_query->sum('transaksi.amount');

            // ===== 3. HITUNG SALDO AWAL DAN AKHIR =====
            // Saldo awal = initial_balance + transaksi sebelum start_date
            $saldo_awal = $dompet->initial_balance + $perubahan_before;
            $saldo_awal_total += $saldo_awal;

            // Saldo akhir = saldo awal + transaksi dalam range
            $saldo_akhir = $saldo_awal + $perubahan_range;
            $saldo_akhir_total += $saldo_akhir;

            // Total transaksi dalam range (langsung gunakan amount karena sudah dengan tanda)
            $total_transaksi += $perubahan_range;
        }

        return [
            'saldo_awal' => $saldo_awal_total,
            'saldo_akhir' => $saldo_akhir_total,
            'total_transaksi' => $total_transaksi
        ];
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
            'amount' => 'required|decimal:0,2',
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

        $kategori = DB::table('kategori')->where('id', $request->category_id)->first();
        if (!$kategori) {
            return response()->json(['Kategori tidak ditemukan'], 404);
        }

        $amount = abs($request->amount);
        if($kategori->kind === 'expense'){
            $amount = -$amount;
        }

        $transaksi = Transaksi::create([
            'dompet_id' => $request->dompet_id,
            'category_id' => $request->category_id,
            'amount' => $amount,
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
        $transaksi = Transaksi::join('dompet', 'transaksi.dompet_id', '=', 'dompet.id')
            ->join('kategori', 'transaksi.category_id', '=', 'kategori.id')
            ->select('transaksi.*', 'dompet.name as dompet_name', 'kategori.name as kategori_name')
            ->where('transaksi.id', $id)
            ->first();
        
        if(!$transaksi){
            return response()->json(['Transaksi tidak ditemukan'], 404);
        }

        // Cek apakah user memiliki izin mengakses transaksi ini
        $dompet = Dompet::find($transaksi->dompet_id);
        if(!$dompet || $dompet->user_id !== Auth::id()){
            return response()->json(['Tidak memiliki izin untuk mengakses transaksi ini!'], 403);
        }

        return new MessageResource($transaksi, '200', 'Data transaksi berhasil diambil');
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
            'amount' => 'sometimes|decimal:0,2',
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

        DB::transaction(function () use ($request, $transaksi) {
        $categoryId = $request->category_id ?? $transaksi->category_id;
        $kategoriBaru = DB::table('kategori')->where('id', $categoryId)->first();
        if (!$kategoriBaru) {
            throw new \Exception('Kategori tidak ditemukan');
        }

        $amountBaru = $request->has('amount') ? abs($request->amount) : abs($transaksi->amount);
        if ($kategoriBaru->kind === 'expense') {
            $amountBaru = -$amountBaru;
        }

        $transaksi->update([
            'dompet_id'   => $request->dompet_id ?? $transaksi->dompet_id,
            'category_id' => $categoryId,
            'trx_date'    => $request->trx_date ?? $transaksi->trx_date,
            'amount'      => $amountBaru,
            'note'        => $request->note ?? $transaksi->note,
        ]);
        });

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

        DB::transaction(function () use ($transaksi) {
            $transaksi->delete();
        });

        return new MessageResource($transaksi, '200', 'Transaksi berhasil dihapus');
    }
}
