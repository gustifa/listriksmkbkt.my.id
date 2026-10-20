<?php

namespace App\Http\Controllers;

use App\Models\UkkPackage;
use App\Models\UkkUnit;
use Illuminate\Http\Request;

class UkkPackageController extends Controller
{
    public function index()
    {
        $packages = UkkPackage::with('units')->orderBy('created_at', 'desc')->get();
        return view('ukk.packages.index', compact('packages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'major_code'    => 'required|string',
            'package_name'  => 'required|string',
            'academic_year' => 'required|string',
        ]);

        UkkPackage::create($request->only(['major_code', 'package_name', 'academic_year']));

        return back()->with('success', 'Paket UKK berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $package = UkkPackage::findOrFail($id);
        $package->delete();

        return back()->with('success', 'Paket UKK berhasil dihapus.');
    }

    public function storeUnit(Request $request, $packageId)
    {
        $request->validate([
            'unit_code'    => 'required|string',
            'unit_title'   => 'required|string',
            'element_name' => 'required|string',
        ]);

        UkkUnit::create([
            'ukk_package_id' => $packageId,
            'unit_code'      => $request->unit_code,
            'unit_title'     => $request->unit_title,
            'element_name'   => $request->element_name,
        ]);

        return back()->with('success', 'Unit kompetensi berhasil ditambahkan.');
    }

    public function destroyUnit($unitId)
    {
        $unit = UkkUnit::findOrFail($unitId);
        $unit->delete();

        return back()->with('success', 'Unit kompetensi berhasil dihapus.');
    }
}