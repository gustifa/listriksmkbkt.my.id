<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class InventoriesImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $rawCode     = trim($row['kode_barang'] ?? '');
        $newQuantity = (int) ($row['stok_tersedia'] ?? 0);
        $name        = trim($row['nama_barang']);

        // 1. Cari Bengkel / Ruangan
        $bengkelName = trim($row['nama_bengkel'] ?? $row['bengkel_ruangan'] ?? $row['bengkel'] ?? '');
        $room = Room::where('name', 'ILIKE', $bengkelName)
                    ->orWhere('code', 'ILIKE', $bengkelName)
                    ->first();

        // 2. Normalisasi Tipe, Kondisi & Sumber Dana
        $rawType  = strtolower(trim($row['tipe_alat_atau_bahan'] ?? $row['tipe'] ?? 'alat'));
        $type     = in_array($rawType, ['bahan', 'bhp']) ? 'bahan' : 'alat';
        
        $rawCond   = strtolower(str_replace(' ', '_', trim($row['kondisi'] ?? 'baik')));
        $condition = in_array($rawCond, ['baik', 'rusak_ringan', 'rusak_berat']) ? $rawCond : 'baik';
        
        $fundingSource = strtoupper(trim($row['sumber_dana'] ?? 'BOS'));
        $year          = (int) ($row['tahun_pengadaan'] ?? date('Y'));

        return DB::transaction(function () use ($rawCode, $name, $room, $type, $condition, $fundingSource, $year, $newQuantity, $row) {
            
            // 3. Cek apakah barang sudah ada (by Kode ATAU by Nama di Ruangan yang sama)
            $existing = null;
            if (!empty($rawCode)) {
                $existing = Inventory::where('code', $rawCode)->first();
            }
            if (!$existing && $room) {
                $existing = Inventory::where('name', 'ILIKE', $name)
                                    ->where('room_id', $room->id)
                                    ->first();
            }

            // JIKA BARANG SUDAH ADA: Tambahkan Stok (Increment)
            if ($existing) {
                $existing->increment('quantity', $newQuantity);

                // Update info pelengkap jika ada
                $existing->update([
                    'condition'        => $condition,
                    'funding_source'   => $fundingSource,
                    'acquisition_year' => $year,
                    'description'      => $row['keterangan'] ?? $existing->description,
                ]);

                // Catat transaksi masuk di inventory_transactions
                InventoryTransaction::create([
                    'item_id'        => $existing->id,
                    'user_id'        => Auth::id() ?? null,
                    'type'           => 'in',
                    'quantity'       => $newQuantity,
                    'date'           => date('Y-m-d'),
                    'notes'          => "Penambahan stok via Import Excel (+{$newQuantity} {$existing->unit})",
                    'funding_source' => $fundingSource,
                    'year'           => $year,
                ]);

                return null;
            }

            // JIKA BARANG BARU: Generate Kode Otomatis bila kode_barang kosong
            $finalCode = $rawCode;
            if (empty($finalCode)) {
                $prefixType = ($type === 'bahan') ? 'BHP' : 'ALT';
                $roomCode   = $room ? ($room->code ?? strtoupper(substr(str_replace(' ', '', $room->name), 0, 3))) : 'GEN';
                $prefix     = "{$prefixType}-{$roomCode}-{$year}-";

                $latest = Inventory::where('code', 'LIKE', "{$prefix}%")
                    ->orderBy('code', 'desc')
                    ->first();

                if ($latest) {
                    $lastNum  = (int) substr($latest->code, -4);
                    $nextNum  = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
                } else {
                    $nextNum  = '0001';
                }

                $finalCode = $prefix . $nextNum;
            }

            // Buat Record Inventaris Baru
            $inventory = Inventory::create([
                'code'             => $finalCode,
                'name'             => $name,
                'room_id'          => $room ? $room->id : null,
                'type'             => $type,
                'category'         => strtolower(trim($row['kategori'] ?? 'umum')),
                'brand_model'      => $row['merk_spesifikasi'] ?? $row['merkspesifikasi'] ?? null,
                'quantity'         => $newQuantity,
                'min_quantity'     => (int) ($row['stok_minimal'] ?? $row['stok_min'] ?? 5),
                'unit'             => trim($row['satuan'] ?? 'pcs'),
                'condition'        => $condition,
                'funding_source'   => $fundingSource,
                'acquisition_year' => $year,
                'description'      => $row['keterangan'] ?? null,
            ]);

            // Catat Transaksi Masuk Stok Awal
            InventoryTransaction::create([
                'item_id'        => $inventory->id,
                'user_id'        => Auth::id() ?? null,
                'type'           => 'in',
                'quantity'       => $newQuantity,
                'date'           => date('Y-m-d'),
                'notes'          => "Pendataan awal via Import Excel ({$newQuantity} {$inventory->unit})",
                'funding_source' => $fundingSource,
                'year'           => $year,
            ]);

            return null;
        });
    }

    public function rules(): array
    {
        return [
            'nama_barang'   => 'required|string',
            'stok_tersedia' => 'required|numeric|min:0',
            'satuan'        => 'required|string',
        ];
    }
}