<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class NotificationController extends Controller
{
    /**
     * Get unread notification count
     */
    public function getUnreadCount()
    {
        /** @var User $user */
        $user = Auth::user();
        $count = $user->notifications()->where('is_read', false)->count();
        
        // Get latest unread notification
        $latest = $user->notifications()
            ->where('is_read', false)
            ->with('triggeredBy')
            ->latest()
            ->first();
        
        $latestData = null;
        if ($latest) {
            $latestData = [
                'id' => $latest->id,
                'icon' => $this->getNotificationIcon($latest->type),
                'type' => $latest->type,
                'title' => $latest->title,
                'message' => $latest->message,
                'created_at' => $latest->created_at->toDateTimeString(),
                'created_at_human' => $latest->created_at->diffForHumans(),
            ];
        }
        
        return response()->json([
            'success' => true,
            'count' => $count,
            'latest' => $latestData,
        ]);
    }
    
    /**
     * Get all notifications (paginated)
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->with('triggeredBy')
            ->latest()
            ->limit(50)
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'icon' => $this->getNotificationIcon($notification->type),
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->toDateTimeString(),
                    'created_at_human' => $notification->created_at->diffForHumans(),
                    'triggered_by' => $notification->triggeredBy ? [
                        'id' => $notification->triggeredBy->id,
                        'name' => $notification->triggeredBy->name,
                        'avatar_url' => $notification->triggeredBy->avatar_url,
                    ] : null,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }
    
    /**
     * Mark single notification as read
     */
    public function markAsRead($id)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $notification = $user->notifications()->findOrFail($id);
        $notification->is_read = true;
        $notification->read_at = now();
        $notification->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        /** @var User $user */
        $user = Auth::user();
        
        $updated = $user->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'updated_count' => $updated,
        ]);
    }
    
    /**
     * Delete single notification (soft delete)
     */
    public function destroy($id)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }
    
    /**
     * 🔥 DELETE ALL UNREAD NOTIFICATIONS (SOFT DELETE) - NEW METHOD
     */
    public function deleteAll()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get count before deleting
        $count = $user->notifications()
            ->where('is_read', false)
            ->count();
        
        // Soft delete all unread notifications
        $user->notifications()
            ->where('is_read', false)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted',
            'deleted_count' => $count,
        ]);
    }
    
    /**
     * Get notification icon based on type
     */
    private function getNotificationIcon($type)
    {
        $icons = [
            'customer_created' => '➕',
            'customer_updated' => '✏️',
            'customer_deleted' => '🗑️',
            'customer_restored' => '↩️',
            'receipt_created' => '🧾',
            'receipt_updated' => '📝',
            'receipt_deleted' => '❌',
            'user_login' => '🔐',
            'user_logout' => '🚪',
            'system' => '⚙️',
        ];
        
        return $icons[$type] ?? '📢';
    }
}