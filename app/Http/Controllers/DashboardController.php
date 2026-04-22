<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\Country;
use App\Models\Company;
use App\Models\Trip;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // ── Shared Data ───────────────────────────────────────────
    private function baseData(): array
    {
        $user = Auth::user();

        if (!$user) {
            return [
                'states'    => State::orderBy('name')->get(),
                'countries' => Country::orderBy('name')->get(),
                'companies' => collect(),
            ];
        }

        return [
            'states'    => State::orderBy('name')->get(),
            'countries' => Country::orderBy('name')->get(),
            'companies' => $this->getCompanies($user),
        ];
    }

    // ── Filter company ikut role ──────────────────────────────
    // Tester  → hanya company yang ditetapkan (users.company_id)
    // Owner   → semua company milik sendiri (companies.user_id)
    private function getCompanies($user)
    {
        if ($user->role === 'tester') {
            return Company::where('id', $user->company_id)
                ->orderBy('name')
                ->get();
        }

        return Company::where('user_id', $user->id)
            ->orderBy('name')
            ->get();
    }

    // ── Dashboard ─────────────────────────────────────────────
    public function index()
    {
        return view('modules.dashboard.index', $this->withView('dashboard'));
    }

    public function addCustomer()
    {
        return view('modules.customers.form', array_merge(
            $this->baseData(),
            ['activeView' => 'add-customer']
        ));
    }

    // ── Companies ─────────────────────────────────────────────
    public function companies()
    {
        return view('modules.companies.index', $this->withView('companies'));
    }

    // ── Trips ────────────────────────────────────────────────
    public function trips()
    {
        return view('modules.trips.index', $this->withView('trips'));
    }

    public function addTrip()
    {
        return view('modules.trips.add', $this->withView('add-trip'));
    }

    public function editTrip($id)
    {
        $trip = Trip::with(['company', 'itineraries'])->findOrFail($id);

        return view('modules.trips.edit', array_merge(
            $this->withView('trips'),
            ['trip' => $trip]
        ));
    }

    public function manageTrip($id)
    {
        $trip = Trip::with('company')->findOrFail($id);

        return view('modules.trips.manage', array_merge(
            $this->withView('trips'),
            ['trip' => $trip]
        ));
    }

    // ── Bookings ─────────────────────────────────────────────
    public function bookings()
    {
        return view('modules.bookings.index', $this->withView('bookings'));
    }

    public function createBooking()
    {
        return view('modules.bookings.create', $this->withView('create-booking'));
    }

    // ── Receipts ─────────────────────────────────────────────
    public function receipts()
    {
        return view('modules.receipts.index', $this->withView('receipts'));
    }

    public function createReceipt()
    {
        return view('modules.receipts.create', $this->withView('create-receipt'));
    }

    // ── Settings ─────────────────────────────────────────────
    public function settings()
    {
        return view('modules.settings.index', $this->withView('settings'));
    }

    // ── Helper untuk reduce duplicate code ───────────────────
    private function withView(string $activeView): array
    {
        return array_merge(
            $this->baseData(),
            ['activeView' => $activeView]
        );
    }
}