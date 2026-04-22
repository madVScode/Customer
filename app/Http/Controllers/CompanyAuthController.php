<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CompanyAuthController extends Controller
{
    // ── Papar halaman login company ───────────────────────────────────────────
    public function showLogin()
    {
        // Jika sudah login sebagai company, redirect ke company dashboard
        if (session('company_auth')) {
            return redirect()->route('company.dashboard');
        }

        return view('company.login');
    }

    // ── Proses login company ──────────────────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'company_id'      => 'required|string',
            'registration_no' => 'required|string',
            'password'        => 'required|string',
        ]);

        $company = Company::find($request->company_id);

        // Semak sama ada company wujud
        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Syarikat tidak dijumpai.',
            ], 401);
        }

        // Semak portal aktif
        if (!$company->portal_active) {
            return response()->json([
                'success' => false,
                'message' => 'Portal syarikat ini belum diaktifkan. Hubungi pentadbir sistem.',
            ], 403);
        }

        // Semak registration_no
        if ($company->registration_no !== $request->registration_no) {
            return response()->json([
                'success' => false,
                'message' => 'No. Pendaftaran tidak sepadan.',
            ], 401);
        }

        // Semak password belum ditetapkan
        if (empty($company->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata laluan belum ditetapkan. Hubungi pentadbir sistem.',
            ], 401);
        }

        // Semak password
        if (!Hash::check($request->password, $company->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata laluan tidak sah.',
            ], 401);
        }

        // ✅ LOGIN BERJAYA — simpan dalam session
        session([
            'company_auth' => [
                'company_id'      => $company->id,
                'company_name'    => $company->name,
                'registration_no' => $company->registration_no,
                'logo'            => $company->logo,
                'logged_at'       => now()->toISOString(),
            ],
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Log masuk berjaya! Mengalih hala...',
            'redirect' => route('company.dashboard'),
        ]);
    }

    // ── Logout company ────────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        session()->forget('company_auth');
        return redirect()->route('company.login');
    }

    // ── API: Senarai semua company aktif (untuk dropdown login) ───────────────
    public function listActive()
    {
        $companies = Company::select('id', 'name', 'registration_no')
            ->where('portal_active', true)
            ->whereNotNull('password')
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $companies]);
    }

    // ── Admin: Set / tukar password company ──────────────────────────────────
    public function setPassword(Request $request, $id)
    {
        // Hanya admin (auth user) boleh set password company
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Tidak dibenarkan.'], 403);
        }

        $request->validate([
            'password'        => 'required|string|min:6',
            'portal_active'   => 'nullable|boolean',
        ]);

        $company = Company::findOrFail($id);
        $company->password      = Hash::make($request->password);
        $company->portal_active = $request->boolean('portal_active', true);
        $company->save();

        return response()->json([
            'success' => true,
            'message' => "Kata laluan untuk {$company->name} berjaya ditetapkan.",
        ]);
    }
}