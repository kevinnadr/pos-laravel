<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::first();
        if (!$setting) {
            $setting = Setting::create([
                'nama_toko' => 'Raket Murah Jogja',
                'alamat_toko' => 'Jl. Olahraga No. 88, Bandung',
                'no_telp' => '081234567890',
                'footer_struk' => 'Terima kasih atas kunjungan Anda!',
                'bank_nama' => 'BCA',
                'bank_rekening' => '4521 8899 0012',
                'bank_atas_nama' => 'Raket Murah Jogja',
            ]);
        }
        return view('pos.setting', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_toko' => 'required',
            'alamat_toko' => 'required',
            'no_telp' => 'required',
            'footer_struk' => 'required',
            'bank_nama' => 'required',
            'bank_rekening' => 'required',
            'bank_atas_nama' => 'required',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'qris_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $setting = Setting::first();
        
        $data = $request->except(['logo', 'qris_image', '_token', '_method']);

        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                Storage::delete('public/' . $setting->logo);
            }
            $path = $request->file('logo')->store('settings', 'public');
            $data['logo'] = $path;
        }

        if ($request->hasFile('qris_image')) {
            if ($setting->qris_image) {
                Storage::delete('public/' . $setting->qris_image);
            }
            $path = $request->file('qris_image')->store('settings', 'public');
            $data['qris_image'] = $path;
        }

        $setting->update($data);

        return redirect()->route('setting')->with('success', 'Pengaturan berhasil diperbarui!');
    }
}
