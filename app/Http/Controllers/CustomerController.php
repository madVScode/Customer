<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\State;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * =========================================================
     * Show Main Customer Page
     * =========================================================
     */
    public function index()
    {
        $states = State::orderBy('name')->get();
        return view('customers.index', compact('states'));
    }

    /**
     * =========================================================
     * Search Customers (Advanced Filter)
     * =========================================================
     */
    public function search(Request $request)
    {
        $query = Customer::with(['address', 'state'])->withTrashed();

        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->filled('ic')) {
            $query->where('ic', 'like', "%{$request->ic}%");
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filter umur berdasarkan DOB
        if ($request->filled('age_min')) {
            $query->whereRaw(
                'TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= ?',
                [$request->age_min]
            );
        }

        if ($request->filled('age_max')) {
            $query->whereRaw(
                'TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= ?',
                [$request->age_max]
            );
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        $customers = $query->latest()->limit(50)->get();

        // ✅ FIX: Format response dengan age dan gender
        $formattedCustomers = $customers->map(function($customer) {
            return [
                'unique_id' => $customer->unique_id,
                'ic' => $customer->ic,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'dob' => $customer->dob,
                'age' => $customer->age, // ✅ IMPORTANT: Include age!
                'gender' => $customer->gender, // ✅ IMPORTANT: Include gender!
                'state_id' => $customer->state_id,
                'state' => $customer->state ? [
                    'id' => $customer->state->id,
                    'name' => $customer->state->name,
                ] : null,
                'address' => $customer->address ? [
                    'address_line' => $customer->address->address_line,
                    'postcode' => $customer->address->postcode,
                ] : null,
                'deleted_at' => $customer->deleted_at,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedCustomers,
            'total' => $query->count()
        ]);
    }

    /**
     * =========================================================
     * Store New Customer
     * =========================================================
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Permission check (ADMIN MANAGE sahaja)
        if (!$user->isAdminManage()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.'
            ], 403);
        }

        // Bersihkan IC
        $request->merge([
            'ic' => preg_replace('/\D/', '', $request->ic)
        ]);

        $validated = $request->validate([
            'ic'           => 'required|digits:12|unique:customers,ic',
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:customers,email',
            'phone'        => 'required|string|max:20',
            'state_id'     => 'required|exists:states,id',
            'address_line' => 'required|string|max:500',
            'postcode'     => 'required|string|max:10',
        ]);

        DB::beginTransaction();

        try {
            // Create customer
            $customer = Customer::create([
                'unique_id' => (string) Str::uuid(),
                'ic'        => $validated['ic'],
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'phone'     => $validated['phone'],
                'state_id'  => $validated['state_id'],
                'dob'       => Customer::extractDobFromIc($validated['ic']),
                'gender'    => Customer::extractGenderFromIc($validated['ic']),
            ]);

            // Create address
            $customer->address()->create([
                'address_line' => $validated['address_line'],
                'postcode'     => $validated['postcode'],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'created',
                'model' => 'Customer',
                'model_id' => $customer->unique_id,
                'description' => $user->name . ' created customer: ' . $customer->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // ✅ FIX: Notify other admins dengan proper structure
            $this->notifyOtherAdmins('created', $customer->name, $customer->unique_id);

            DB::commit();

            // ✅ Return customer dengan age dan gender
            return response()->json([
                'success' => true,
                'message' => 'Customer berjaya ditambah!',
                'data' => [
                    'unique_id' => $customer->unique_id,
                    'ic' => $customer->ic,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'dob' => $customer->dob,
                    'age' => $customer->age,
                    'gender' => $customer->gender,
                    'state_id' => $customer->state_id,
                    'state' => $customer->state,
                    'address' => $customer->address,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * =========================================================
     * Update Customer
     * =========================================================
     */
    public function update(Request $request, $unique_id)
    {
        /** @var User $user */
        $user = Auth::user();

        // Permission check
        if (!$user->isAdminManage()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.'
            ], 403);
        }

        $customer = Customer::where('unique_id', $unique_id)->firstOrFail();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:customers,email,' . $customer->unique_id . ',unique_id',
            'phone'    => 'required|string|max:20',
            'state_id' => 'required|exists:states,id',
            'address_line' => 'required|string|max:500',
            'postcode' => 'required|string|max:10',
        ]);

        DB::beginTransaction();

        try {
            $customer->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'state_id' => $validated['state_id'],
            ]);

            // Update or create address
            if ($customer->address) {
                $customer->address->update([
                    'address_line' => $validated['address_line'],
                    'postcode' => $validated['postcode'],
                ]);
            } else {
                $customer->address()->create([
                    'address_line' => $validated['address_line'],
                    'postcode' => $validated['postcode'],
                ]);
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'updated',
                'model' => 'Customer',
                'model_id' => $customer->unique_id,
                'description' => $user->name . ' updated customer: ' . $customer->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // ✅ Notify other admins
            $this->notifyOtherAdmins('updated', $customer->name, $customer->unique_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer berjaya dikemaskini!',
                'data' => [
                    'unique_id' => $customer->unique_id,
                    'ic' => $customer->ic,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'age' => $customer->age,
                    'gender' => $customer->gender,
                    'state' => $customer->state,
                    'address' => $customer->address,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * =========================================================
     * Delete Customer (Soft Delete)
     * =========================================================
     */
    public function destroy($unique_id)
    {
        /** @var User $user */
        $user = Auth::user();

        // Permission check
        if (!$user->isAdminManage()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.'
            ], 403);
        }

        $customer = Customer::where('unique_id', $unique_id)->firstOrFail();
        $customerName = $customer->name;
        
        $customer->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'deleted',
            'model' => 'Customer',
            'model_id' => $unique_id,
            'description' => $user->name . ' deleted customer: ' . $customerName,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // ✅ Notify other admins
        $this->notifyOtherAdmins('deleted', $customerName, $unique_id);

        return response()->json([
            'success' => true,
            'message' => 'Customer berjaya dipadam!'
        ]);
    }

    /**
     * =========================================================
     * Restore Deleted Customer
     * =========================================================
     */
    public function restore($unique_id)
    {
        Customer::withTrashed()
            ->where('unique_id', $unique_id)
            ->firstOrFail()
            ->restore();

        return response()->json([
            'success' => true,
            'message' => 'Customer berjaya dipulihkan!'
        ]);
    }

    /**
     * =========================================================
     * Live Search (AJAX)
     * =========================================================
     */
    public function searchAjax(Request $request)
    {
        $query = $request->input('query');

        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('ic', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    /**
     * =========================================================
     * Helper: Notify Other Admins
     * =========================================================
     */
  private function notifyOtherAdmins($action, $customerName, $customerId)
{
    /** @var User $currentUser */
    $currentUser = Auth::user();

    // 🔥 Target hanya user role admin_record
    $targetAdmins = User::where('role', 'admin_record')
                        ->where('id', '!=', $currentUser->id)
                        ->get();

    $messages = [
        'created' => $currentUser->name . ' menambah customer baru: ' . $customerName,
        'updated' => $currentUser->name . ' mengemaskini customer: ' . $customerName,
        'deleted' => $currentUser->name . ' memadam customer: ' . $customerName,
    ];

    $titles = [
        'created' => 'Customer Baru Ditambah',
        'updated' => 'Customer Dikemaskini',
        'deleted' => 'Customer Dipadam',
    ];

    $types = [
        'created' => 'customer_created',
        'updated' => 'customer_updated',
        'deleted' => 'customer_deleted',
    ];

    foreach ($targetAdmins as $admin) {
        Notification::create([
            'user_id' => $admin->id,
            'triggered_by' => $currentUser->id,
            'type' => $types[$action],
            'title' => $titles[$action],
            'message' => $messages[$action],
            'data' => json_encode(['customer_id' => $customerId]),
            'is_read' => false,
        ]);
    }
}




}