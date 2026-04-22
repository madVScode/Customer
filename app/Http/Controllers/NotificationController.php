<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    // =========================================================
    // GET /notifications/unread-count
    // =========================================================
    public function getUnreadCount()
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => true, 'count' => 0, 'latest' => null]);
            }

            $count  = Notification::where('user_id', $user->id)->where('is_read', false)->count();
            $latest = Notification::where('user_id', $user->id)->where('is_read', false)->latest()->first();

            return response()->json([
                'success' => true,
                'count'   => $count,
                'latest'  => $latest ? [
                    'id'               => $latest->id,
                    'icon'             => self::getNotificationIcon($latest->type),
                    'type'             => $latest->type,
                    'title'            => $latest->title,
                    'message'          => $latest->message,
                    'created_at_human' => $latest->created_at->diffForHumans(),
                ] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('NotificationController@getUnreadCount: ' . $e->getMessage());
            return response()->json(['success' => true, 'count' => 0, 'latest' => null]);
        }
    }

    // =========================================================
    // GET /notifications
    // =========================================================
    public function index()
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $notifications = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(fn($n) => [
                    'id'               => $n->id,
                    'icon'             => self::getNotificationIcon($n->type),
                    'type'             => $n->type,
                    'title'            => $n->title,
                    'message'          => $n->message,
                    'is_read'          => (bool) $n->is_read,
                    'created_at_human' => $n->created_at->diffForHumans(),
                    'data'             => $n->data,
                ]);

            return response()->json(['success' => true, 'data' => $notifications]);
        } catch (\Exception $e) {
            Log::error('NotificationController@index: ' . $e->getMessage());
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    // =========================================================
    // POST /notifications/{id}/read
    // =========================================================
    public function markAsRead($id)
    {
        try {
            $notif = Notification::where('id', $id)->where('user_id', Auth::id())->first();
            if ($notif) {
                $notif->update(['is_read' => true, 'read_at' => now()]);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => true]);
        }
    }

    // =========================================================
    // POST /notifications/read-all
    // =========================================================
    public function markAllAsRead()
    {
        try {
            Notification::where('user_id', Auth::id())->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => true]);
        }
    }

    // =========================================================
    // DELETE /notifications/{id}
    // =========================================================
    public function destroy($id)
    {
        try {
            Notification::where('id', $id)->where('user_id', Auth::id())->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => true]);
        }
    }

    // =========================================================
    // POST /notifications/delete-all
    // =========================================================
    public function deleteAll()
    {
        try {
            $count = Notification::where('user_id', Auth::id())->where('is_read', false)->count();
            Notification::where('user_id', Auth::id())->where('is_read', false)->delete();
            return response()->json(['success' => true, 'deleted_count' => $count]);
        } catch (\Exception $e) {
            Log::error('NotificationController@deleteAll: ' . $e->getMessage());
            return response()->json(['success' => false]);
        }
    }

    // =========================================================
    // STATIC HELPERS — dipanggil dari CustomerController dll.
    // =========================================================

    /**
     * Hantar notification ke staff yang sesuai berdasarkan role dan company.
     * 
     * Rules:
     * - admin_system: dapat SEMUA notification
     * - owner_business: dapat login/logout admin + customer changes untuk company mereka sahaja
     * - tester: dapat login/logout admin + customer changes untuk company mereka sahaja
     */
    public static function notifyStaff(
        int     $excludeUserId,
        string  $type,
        string  $title,
        string  $message,
        array   $data = [],
        int|string|null $companyId = null,
        ?string $companyName = null
    ): void {
        try {
            $triggeredBy = User::find($excludeUserId);
            $triggeredByRole = $triggeredBy ? $triggeredBy->role : 'unknown';
            
            // Tambah maklumat role ke dalam message jika ada triggeredBy
            if ($triggeredBy) {
                $roleLabel = self::getRoleLabel($triggeredByRole);
                $message = "[{$roleLabel}] " . $message;
            }
            
            // Tambah maklumat company jika ada
            if ($companyName) {
                $message .= " (Company: {$companyName})";
            }
            
            // Dapatkan semua staff yang berpotensi menerima notification
            $allStaff = User::whereIn('role', ['admin_system', 'owner_business', 'tester'])
                ->where('id', '!=', $excludeUserId)
                ->get();

            foreach ($allStaff as $user) {
                $shouldNotify = self::shouldReceiveNotification($user, $type, $companyId);
                
                if ($shouldNotify) {
                    Notification::create([
                        'user_id'      => $user->id,
                        'triggered_by' => $excludeUserId,
                        'type'         => $type,
                        'title'        => $title,
                        'message'      => $message,
                        'data'         => array_merge($data ?: [], [
                            'company_id'   => $companyId,
                            'company_name' => $companyName,
                            'triggered_by_role' => $triggeredByRole,
                        ]),
                        'is_read'      => false,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('NotificationController::notifyStaff: ' . $e->getMessage());
        }
    }

    /**
     * Tentukan sama ada user patut terima notification berdasarkan role dan type
     */
    private static function shouldReceiveNotification(User $user, string $type, int|string|null $companyId): bool
    {
        // admin_system dapat SEMUA notification
        if ($user->role === 'admin_system') {
            return true;
        }

        // owner_business dan tester - filter berdasarkan type dan company
        if (in_array($user->role, ['owner_business', 'tester'])) {
            
            // Dapatkan notification untuk login/logout admin_system
            if (in_array($type, ['login', 'admin_login', 'logout', 'user_logout'])) {
                return true;
            }
            
            // Dapatkan notification untuk customer changes jika company sama
            if (in_array($type, ['customer_created', 'customer_updated', 'customer_deleted', 'customer_restored'])) {
                // Check jika user ini belong ke company yang sama
                if ($companyId && $user->company_id == $companyId) {
                    return true;
                }
            }
            
            // Tidak dapat notification lain
            return false;
        }

        return false;
    }

    /**
     * Dapatkan label role dalam Bahasa Malaysia
     */
    private static function getRoleLabel(string $role): string
    {
        return match($role) {
            'admin_system' => 'Admin',
            'owner_business' => 'Owner',
            'tester' => 'Tester',
            'customer' => 'Customer',
            default => ucfirst($role),
        };
    }

    /**
     * Hantar notification untuk login/logout admin ke semua owner & tester
     */
    public static function notifyAdminLoginLogout(
        int    $adminUserId,
        string $type,
        string $title,
        string $message
    ): void {
        try {
            $admin = User::find($adminUserId);
            if (!$admin || $admin->role !== 'admin_system') {
                return;
            }

            $roleLabel = self::getRoleLabel($admin->role);
            $fullMessage = "[{$roleLabel}] " . $message;

            // Hantar ke semua owner_business dan tester
            $recipients = User::whereIn('role', ['owner_business', 'tester', 'admin_system'])
                ->where('id', '!=', $adminUserId)
                ->get();

            foreach ($recipients as $user) {
                Notification::create([
                    'user_id'      => $user->id,
                    'triggered_by' => $adminUserId,
                    'type'         => $type,
                    'title'        => $title,
                    'message'      => $fullMessage,
                    'data'         => ['triggered_by_role' => 'admin_system'],
                    'is_read'      => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('NotificationController::notifyAdminLoginLogout: ' . $e->getMessage());
        }
    }

    /**
     * Hantar notification ke satu user spesifik.
     */
    public static function sendTo(
        int    $userId,
        int    $triggeredById,
        string $type,
        string $title,
        string $message,
        array  $data = []
    ): void {
        try {
            Notification::create([
                'user_id'      => $userId,
                'triggered_by' => $triggeredById,
                'type'         => $type,
                'title'        => $title,
                'message'      => $message,
                'data'         => $data ?: null,
                'is_read'      => false,
            ]);
        } catch (\Exception $e) {
            Log::error('NotificationController::sendTo: ' . $e->getMessage());
        }
    }

    // =========================================================
    // Icon mapping
    // =========================================================
    public static function getNotificationIcon(string $type): string
    {
        return [
            'customer_created'   => '👤➕',
            'customer_updated'   => '👤✏️',
            'customer_deleted'   => '👤🗑️',
            'customer_restored'  => '👤↩️',
            'receipt_created'    => '🧾➕',
            'receipt_updated'    => '🧾✏️',
            'receipt_deleted'    => '🧾🗑️',
            'receipt_restored'   => '🧾↩️',
            'booking_created'    => '📝➕',
            'booking_updated'    => '📝✏️',
            'booking_deleted'    => '📝🗑️',
            'trip_created'       => '✈️➕',
            'trip_updated'       => '✈️✏️',
            'trip_deleted'       => '✈️🗑️',
            'company_created'    => '🏢➕',
            'company_updated'    => '🏢✏️',
            'company_deleted'    => '🏢🗑️',
            'login'              => '🔐',
            'admin_login'        => '🔐',
            'logout'             => '🚪',
            'password_changed'   => '🔑',
            'user_login'         => '🔐',
            'user_logout'        => '🚪',
        ][$type] ?? '📢';
    }
}