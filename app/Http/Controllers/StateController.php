<?php
// app/Http/Controllers/StateController.php
namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\Request;

class StateController extends Controller
{
    /**
     * Dapatkan semua state.
     * Boleh return JSON (API) atau return view dengan $states.
     */
    public function index(Request $request)
    {
        $states = State::all();

        // Jika request AJAX, return JSON
        if ($request->ajax()) {
            return response()->json($states);
        }

        // Jika bukan AJAX, boleh hantar terus ke view
        return view('modules.states.index', compact('states'));
    }
}