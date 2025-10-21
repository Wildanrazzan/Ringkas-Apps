<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $kategori = Kategori::where('user_id', $user->id)
            ->orWhereNull('user_id')
            ->get();
        
        return new MessageResource($kategori, '200', 'Data kategori berhasil diambil');
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'kind' => 'required|in:income,expense',
            'icon' => 'nullable|string|max:40',
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),442);
        }

        $kategori = Kategori::create([
            'name' => $request->name,
            'kind' => $request->kind,
            'icon' => $request->icon ?? 'tag',
            'color' => $request->color ?? '#000000',
            'user_id' => Auth::id()
        ]);

        return new MessageResource($kategori, '201', 'Kategori berhasil dibuat');
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
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'kind' => 'sometimes|in: income, expense',
            'icon' => 'sometimes|string|max:40',
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),442);
        }

        $kategori = Kategori::find($id);

        if(!$kategori){
            return response()->json(['Kategori tidak ditemukan']);
        }

        if($kategorit->user_id !== Auth::id()){
            return response()->json(['Tidak boleh update kategori yang bukan milik anda!'], 403);
        }

        $kkategori->update($request->only([
            'name',
            'kind',
            'icon',
            'color'
        ]));

        return new MessageResource($kategori, '200', 'Kategori berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kategori = Kategori::find($id);

        if(!$kategori){
            return response()->json(['Kategori tidak ditemukan']);
        }

        if($kategori->user_id !== Auth::id()){
            return response()->json(['Tidak boleh update kategori yang bukan milik anda!'], 403);
        }

        $kategori->delete();

        return new MessageResource($kategori, '200', 'Kategori berhasil dihapus');
    }
}
