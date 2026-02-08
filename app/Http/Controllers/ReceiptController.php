<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReceiptController extends Controller
{
    /**
     * Display a listing of receipts with customer data
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $receipts = Receipt::with(['customer' => function($query) {
                $query->select('unique_id', 'name', 'ic', 'email', 'phone', 'dob');
            }])
            ->orderBy('issued_at', 'desc')
            ->get()
            ->map(function($receipt) {
                return [
                    'id' => $receipt->id,
                    'receipt_no' => $receipt->receipt_no,
                    'issued_at' => $receipt->issued_at,
                    'total_amount' => $receipt->total_amount,
                    'payment_method' => $receipt->payment_method,
                    'notes' => $receipt->notes,
                    'customer' => $receipt->customer ? [
                        'unique_id' => $receipt->customer->unique_id,
                        'name' => $receipt->customer->name,
                        'ic' => $receipt->customer->ic,
                        'email' => $receipt->customer->email,
                        'phone' => $receipt->customer->phone,
                        'age' => $receipt->customer->age, // Include age
                    ] : null,
                    'created_at' => $receipt->created_at,
                    'updated_at' => $receipt->updated_at,
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $receipts,
                'count' => $receipts->count()
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error fetching receipts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load receipts. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created receipt
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,unique_id',
                'receipt_no' => 'required|string|max:255|unique:receipts,receipt_no',
                'issued_at' => 'required|date',
                'total_amount' => 'required|numeric|min:0|max:999999.99',
                'payment_method' => 'required|in:cash,online_banking,e-wallet,credit_card,debit_card',
                'notes' => 'nullable|string|max:1000'
            ], [
                'customer_id.required' => 'Please select a customer',
                'customer_id.exists' => 'Selected customer does not exist',
                'receipt_no.unique' => 'Receipt number already exists',
                'total_amount.required' => 'Please enter the payment amount',
                'total_amount.min' => 'Amount must be greater than 0',
                'payment_method.required' => 'Please select a payment method',
                'payment_method.in' => 'Invalid payment method selected'
            ]);

            // Start transaction
            DB::beginTransaction();

            // Create receipt
            $receipt = Receipt::create($validated);
            
            // Load customer relationship with age
            $receipt->load(['customer' => function($query) {
                $query->select('unique_id', 'name', 'ic', 'email', 'phone', 'dob');
            }]);

            // Format response with age
            $responseData = [
                'id' => $receipt->id,
                'receipt_no' => $receipt->receipt_no,
                'issued_at' => $receipt->issued_at,
                'total_amount' => $receipt->total_amount,
                'payment_method' => $receipt->payment_method,
                'notes' => $receipt->notes,
                'customer' => $receipt->customer ? [
                    'unique_id' => $receipt->customer->unique_id,
                    'name' => $receipt->customer->name,
                    'ic' => $receipt->customer->ic,
                    'email' => $receipt->customer->email,
                    'phone' => $receipt->customer->phone,
                    'age' => $receipt->customer->age,
                ] : null,
            ];
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Receipt created successfully'
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating receipt: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create receipt. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified receipt
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $receipt = Receipt::with(['customer' => function($query) {
                $query->select('unique_id', 'name', 'ic', 'email', 'phone', 'dob');
            }])
            ->findOrFail($id);

            $responseData = [
                'id' => $receipt->id,
                'receipt_no' => $receipt->receipt_no,
                'issued_at' => $receipt->issued_at,
                'total_amount' => $receipt->total_amount,
                'payment_method' => $receipt->payment_method,
                'notes' => $receipt->notes,
                'customer' => $receipt->customer ? [
                    'unique_id' => $receipt->customer->unique_id,
                    'name' => $receipt->customer->name,
                    'ic' => $receipt->customer->ic,
                    'email' => $receipt->customer->email,
                    'phone' => $receipt->customer->phone,
                    'age' => $receipt->customer->age,
                ] : null,
            ];
            
            return response()->json([
                'success' => true,
                'data' => $responseData
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Receipt not found'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Error fetching receipt: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load receipt',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified receipt
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $receipt = Receipt::findOrFail($id);
            
            // Validate request
            $validated = $request->validate([
                'customer_id' => 'sometimes|exists:customers,unique_id',
                'receipt_no' => 'sometimes|string|max:255|unique:receipts,receipt_no,' . $id,
                'issued_at' => 'sometimes|date',
                'total_amount' => 'sometimes|numeric|min:0|max:999999.99',
                'payment_method' => 'sometimes|in:cash,online_banking,e-wallet,credit_card,debit_card',
                'notes' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            // Update receipt
            $receipt->update($validated);
            
            // Load customer relationship
            $receipt->load(['customer' => function($query) {
                $query->select('unique_id', 'name', 'ic', 'email', 'phone', 'dob');
            }]);

            $responseData = [
                'id' => $receipt->id,
                'receipt_no' => $receipt->receipt_no,
                'issued_at' => $receipt->issued_at,
                'total_amount' => $receipt->total_amount,
                'payment_method' => $receipt->payment_method,
                'notes' => $receipt->notes,
                'customer' => $receipt->customer ? [
                    'unique_id' => $receipt->customer->unique_id,
                    'name' => $receipt->customer->name,
                    'ic' => $receipt->customer->ic,
                    'email' => $receipt->customer->email,
                    'phone' => $receipt->customer->phone,
                    'age' => $receipt->customer->age,
                ] : null,
            ];
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Receipt updated successfully'
            ], 200);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Receipt not found'
            ], 404);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating receipt: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update receipt',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified receipt (soft delete)
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $receipt = Receipt::findOrFail($id);
            
            DB::beginTransaction();
            
            $receipt->delete(); // Soft delete
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Receipt deleted successfully'
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Receipt not found'
            ], 404);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting receipt: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete receipt',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Restore soft deleted receipt
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        try {
            $receipt = Receipt::withTrashed()->findOrFail($id);
            
            if (!$receipt->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receipt is not deleted'
                ], 400);
            }
            
            DB::beginTransaction();
            
            $receipt->restore();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Receipt restored successfully'
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Receipt not found'
            ], 404);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring receipt: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore receipt',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get receipts by customer
     * 
     * @param  string  $customerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCustomer($customerId)
    {
        try {
            $receipts = Receipt::with(['customer' => function($query) {
                $query->select('unique_id', 'name', 'ic', 'email', 'phone', 'dob');
            }])
                ->where('customer_id', $customerId)
                ->orderBy('issued_at', 'desc')
                ->get()
                ->map(function($receipt) {
                    return [
                        'id' => $receipt->id,
                        'receipt_no' => $receipt->receipt_no,
                        'issued_at' => $receipt->issued_at,
                        'total_amount' => $receipt->total_amount,
                        'payment_method' => $receipt->payment_method,
                        'notes' => $receipt->notes,
                        'customer' => $receipt->customer ? [
                            'unique_id' => $receipt->customer->unique_id,
                            'name' => $receipt->customer->name,
                            'ic' => $receipt->customer->ic,
                            'age' => $receipt->customer->age,
                        ] : null,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $receipts,
                'count' => $receipts->count()
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error fetching customer receipts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load customer receipts',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get receipts statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_receipts' => Receipt::count(),
                'total_amount' => Receipt::sum('total_amount'),
                'today_receipts' => Receipt::whereDate('issued_at', today())->count(),
                'today_amount' => Receipt::whereDate('issued_at', today())->sum('total_amount'),
                'payment_methods' => Receipt::select('payment_method', DB::raw('count(*) as count'))
                    ->groupBy('payment_method')
                    ->get(),
                'recent_receipts' => Receipt::with('customer')
                    ->orderBy('issued_at', 'desc')
                    ->limit(5)
                    ->get()
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error fetching receipt statistics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Search receipts with filters
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $query = Receipt::with(['customer' => function($q) {
                $q->select('unique_id', 'name', 'ic', 'email', 'phone', 'dob');
            }]);

            // Search by receipt number
            if ($request->filled('receipt_no')) {
                $query->where('receipt_no', 'like', '%' . $request->receipt_no . '%');
            }

            // Search by customer name
            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->customer_name . '%');
                });
            }

            // Search by date range
            if ($request->filled('date_from')) {
                $query->whereDate('issued_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('issued_at', '<=', $request->date_to);
            }

            // Search by payment method
            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            // Search by amount range
            if ($request->filled('amount_min')) {
                $query->where('total_amount', '>=', $request->amount_min);
            }
            if ($request->filled('amount_max')) {
                $query->where('total_amount', '<=', $request->amount_max);
            }

            $receipts = $query->orderBy('issued_at', 'desc')->get()
                ->map(function($receipt) {
                    return [
                        'id' => $receipt->id,
                        'receipt_no' => $receipt->receipt_no,
                        'issued_at' => $receipt->issued_at,
                        'total_amount' => $receipt->total_amount,
                        'payment_method' => $receipt->payment_method,
                        'notes' => $receipt->notes,
                        'customer' => $receipt->customer ? [
                            'unique_id' => $receipt->customer->unique_id,
                            'name' => $receipt->customer->name,
                            'ic' => $receipt->customer->ic,
                            'age' => $receipt->customer->age,
                        ] : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $receipts,
                'count' => $receipts->count()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error searching receipts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to search receipts',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}