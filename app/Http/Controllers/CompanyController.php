<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /* ─────────────────────────────────────────────────────────
     * Helper: Dapatkan senarai company ID berdasarkan role
     * ───────────────────────────────────────────────────────── */
    private function getCompanyIds(): array
    {
        $user = Auth::user();

        // Admin system nampak semua company
        if ($user->role === 'admin_system') {
            return Company::pluck('id')->toArray();
        }

        // Tester hanya nampak company yang ditetapkan
        if ($user->role === 'tester') {
            return $user->company_id ? [$user->company_id] : [];
        }

        // Owner business tengok company sendiri
        if ($user->role === 'owner_business' && !empty($user->company_id)) {
            return [$user->company_id];
        }

        // Fallback: user biasa
        return Company::where('user_id', $user->id)->pluck('id')->toArray();
    }

    /* ─────────────────────────────────────────────────────────
     * Index: Senarai semua company
     * ───────────────────────────────────────────────────────── */
    public function index()
    {
        $companies = Company::whereIn('id', $this->getCompanyIds())
            ->orderBy('name')
            ->get()
            ->map(fn($c) => $this->formatCompany($c));

        return response()->json(['success' => true, 'data' => $companies]);
    }

    /* ─────────────────────────────────────────────────────────
     * Store: Tambah company baru
     * ───────────────────────────────────────────────────────── */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_name'      => 'nullable|string|max:255',
            'owner_email'     => 'nullable|email|unique:users,email',
            'owner_password'  => 'nullable|string|min:6',
            'owner_phone'     => 'nullable|string|max:20',
            'owner_active'    => 'nullable|boolean',
            'owner_role'      => 'nullable|in:owner_business,tester',
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'registration_no' => 'required|string|unique:companies,registration_no',
            'address'         => 'required|string',
            'logo'            => 'nullable|image|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('companies/logos', 'public');
        }

        /** @var Company $company */
        $company = Company::create([
            'id'              => Str::uuid(),
            'ref_id'          => $this->generateRefId(),
            'user_id'         => Auth::id(),
            'name'            => $validated['name'],
            'phone'           => $validated['phone'] ?? null,
            'registration_no' => $validated['registration_no'],
            'address'         => $validated['address'],
            'logo'            => $logoPath,
        ]);

        // Buat akaun user jika email & password diisi
        if (!empty($validated['owner_email']) && !empty($validated['owner_password'])) {
            User::create([
                'unique_id'  => Str::uuid()->toString(),
                'name'       => $validated['owner_name'] ?? 'Owner',
                'email'      => $validated['owner_email'],
                'password'   => Hash::make($validated['owner_password']),
                'role'       => $validated['owner_role'] ?? 'owner_business',
                'company_id' => $company->id,
                'phone'      => $validated['owner_phone'] ?? null,
            ]);
        }

        $this->handlePortalPassword($request, $company);

        return response()->json([
            'success' => true,
            'data'    => $this->formatCompany($company->fresh()),
            'message' => 'Syarikat berjaya ditambah',
        ]);
    }

    /* ─────────────────────────────────────────────────────────
     * Search: Cari company mengikut nama
     * ───────────────────────────────────────────────────────── */
    public function search(Request $request)
    {
        $q = $request->input('q', '');

        $companies = Company::whereIn('id', $this->getCompanyIds())
            ->where('name', 'like', '%' . $q . '%')
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(fn($c) => $this->formatCompany($c));

        return response()->json(['success' => true, 'data' => $companies]);
    }

    /* ─────────────────────────────────────────────────────────
     * Update: Kemaskini company
     * ───────────────────────────────────────────────────────── */
    public function update(Request $request, $id)
    {
        /** @var Company $company */
        $company = Company::findOrFail($id);

        $existingOwner = User::where('company_id', $id)
            ->whereIn('role', ['owner_business', 'tester'])
            ->first();

        $existingOwnerId = optional($existingOwner)->id;

        $validated = $request->validate([
            'owner_name'      => 'nullable|string|max:255',
            'owner_email'     => 'nullable|email|unique:users,email,' . $existingOwnerId,
            'owner_password'  => 'nullable|string|min:6',
            'owner_phone'     => 'nullable|string|max:20',
            'owner_active'    => 'nullable|boolean',
            'owner_role'      => 'nullable|in:owner_business,tester',
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'registration_no' => 'required|string|unique:companies,registration_no,' . $id,
            'address'         => 'required|string',
            'logo'            => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('companies/logos', 'public');
        }

        $company->update($validated);

        // Update atau create owner/tester
        if (!empty($validated['owner_email'])) {
            $owner = User::where('company_id', $id)
                         ->whereIn('role', ['owner_business', 'tester'])
                         ->first();

            if ($owner) {
                $owner->name  = $validated['owner_name'] ?? $owner->name;
                $owner->email = $validated['owner_email'];
                $owner->phone = $validated['owner_phone'] ?? $owner->phone;
                $owner->role  = $validated['owner_role'] ?? $owner->role;

                if (!empty($validated['owner_password'])) {
                    $owner->password = Hash::make($validated['owner_password']);
                }

                $owner->save();
            } elseif (!empty($validated['owner_password'])) {
                User::create([
                    'unique_id'  => Str::uuid()->toString(),
                    'name'       => $validated['owner_name'] ?? 'Owner',
                    'email'      => $validated['owner_email'],
                    'password'   => Hash::make($validated['owner_password']),
                    'role'       => $validated['owner_role'] ?? 'owner_business',
                    'company_id' => $company->id,
                    'phone'      => $validated['owner_phone'] ?? null,
                ]);
            }
        }

        $this->handlePortalPassword($request, $company);

        return response()->json([
            'success' => true,
            'data'    => $this->formatCompany($company->fresh()),
            'message' => 'Syarikat berjaya dikemaskini',
        ]);
    }

    /* ─────────────────────────────────────────────────────────
     * Destroy: Padam company
     * ───────────────────────────────────────────────────────── */
    public function destroy($id)
    {
        /** @var Company $company */
        $company = Company::findOrFail($id);

        if ($company->is_protected) {
            return response()->json([
                'success' => false,
                'message' => '🔒 Company demo ini tidak boleh dipadam.',
            ], 403);
        }

        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return response()->json([
            'success' => true,
            'message' => 'Syarikat berjaya dipadam',
        ]);
    }

    /* ─────────────────────────────────────────────────────────
     * Set Portal Password (Admin sahaja)
     * ───────────────────────────────────────────────────────── */
    public function setPortalPassword(Request $request, $id)
    {
        $request->validate([
            'password'      => 'required|string|min:6',
            'portal_active' => 'nullable|boolean',
        ]);

        $company = Company::findOrFail($id);

        $company->portal_password = Hash::make($request->password);
        $company->portal_active   = $request->boolean('portal_active', true);
        $company->save();

        return response()->json([
            'success' => true,
            'message' => "Kata laluan portal untuk '{$company->name}' berjaya ditetapkan.",
            'data'    => [
                'portal_active' => $company->portal_active,
                'has_password'  => true,
            ],
        ]);
    }

    /* ─────────────────────────────────────────────────────────
     * Toggle Portal Active
     * ───────────────────────────────────────────────────────── */
    public function togglePortal($id)
    {
        /** @var Company $company */
        $company = Company::findOrFail($id);

        if (empty($company->portal_password)) {
            return response()->json([
                'success' => false,
                'message' => 'Sila tetapkan kata laluan dahulu sebelum mengaktifkan portal.',
            ], 422);
        }

        $company->portal_active = !$company->portal_active;
        $company->save();

        return response()->json([
            'success' => true,
            'message' => $company->portal_active
                ? "Portal '{$company->name}' telah diaktifkan."
                : "Portal '{$company->name}' telah dinyahaktifkan.",
            'data'    => ['portal_active' => $company->portal_active],
        ]);
    }

    /* ─────────────────────────────────────────────────────────
     * Helper: Uruskan portal password (digunakan dalam store & update)
     * ───────────────────────────────────────────────────────── */
    private function handlePortalPassword(Request $request, Company $company): void
    {
        if ($request->filled('portal_password')) {
            $pw = $request->input('portal_password');
            if (strlen($pw) >= 6) {
                $company->portal_password = bcrypt($pw);
                $company->portal_active   = $request->boolean('portal_active', false);
                $company->save();
            }
        } elseif ($request->has('portal_active')) {
            $company->portal_active = $request->boolean('portal_active', false);
            $company->save();
        }
    }

    /* ─────────────────────────────────────────────────────────
     * Helper: Format data company untuk response JSON
     * ───────────────────────────────────────────────────────── */
    private function formatCompany(Company $company): array
    {
        $owner = User::where('company_id', $company->id)
                     ->whereIn('role', ['owner_business', 'tester'])
                     ->first(['unique_id', 'name', 'email', 'phone', 'role']);

        return [
            'id'              => $company->id,
            'ref_id'          => $company->ref_id,
            'name'            => $company->name,
            'phone'           => $company->phone,
            'registration_no' => $company->registration_no,
            'address'         => $company->address,
            'logo_url'        => $company->logo
                                 ? url(Storage::url($company->logo))
                                 : asset('images/default-company.png'),
            'portal_active'   => (bool) $company->portal_active,
            'has_password'    => !empty($company->portal_password),
            'owner'           => $owner ? [
                'unique_id' => $owner->unique_id,
                'name'      => $owner->name,
                'email'     => $owner->email,
                'phone'     => $owner->phone,
                'role'      => $owner->role,
            ] : null,
        ];
    }

    /* ─────────────────────────────────────────────────────────
     * Helper: Jana Ref ID unik untuk company baru
     * ───────────────────────────────────────────────────────── */
    private function generateRefId(): string
    {
        $count = Company::count() + 1;
        return 'AGN-' . date('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}