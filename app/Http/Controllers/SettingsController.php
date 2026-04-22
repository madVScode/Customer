<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        return view('modules.settings.index');
    }

    public function count()
    {
        $user = Auth::user();

        $query = Customer::whereNull('deleted_at');

        if ($user->role !== 'admin_system' && $user->role !== 'tester') {
            if (!empty($user->company_id)) {
                $query->where('company_id', $user->company_id);
            }
        }

        return response()->json(['success' => true, 'count' => $query->count()]);
    }
}