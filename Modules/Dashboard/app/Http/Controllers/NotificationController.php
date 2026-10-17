<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Dashboard\Models\Notification;
use Modules\Dashboard\Services\NotificationService;

/**
 * Controller notifikasi tipis berbasis service (Clean Architecture).
 */
class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Halaman "Lihat Semua Notifikasi".
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $filter = $request->string('filter')->toString();
        $perPage = max(5, (int) config('dashboard.notifications.per_page', 15));

        $notifications = $this->notificationService->getPaginatedNotifications($userId, $filter, $perPage);
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        return view('dashboard::pages.notifications.index', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
            'filter'        => $filter,
        ]);
    }

    /**
     * Snapshot notifikasi terbaru untuk dropdown navbar.
     */
    public function latest(Request $request): JsonResponse
    {
        $perPage = max(5, (int) config('dashboard.notifications.per_page', 15));
        $payload = $this->notificationService->getLatestPayload($request->user()->id, $perPage);

        return response()->json($payload);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function read(Request $request, Notification $notification): JsonResponse
    {
        $unreadCount = $this->notificationService->markAsRead($notification, $request->user()->id);

        return response()->json([
            'success'      => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function readAll(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user()->id);

        return response()->json([
            'success'      => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Buang satu notifikasi.
     */
    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        $unreadCount = $this->notificationService->destroyNotification($notification, $request->user()->id);

        return response()->json([
            'success'      => true,
            'unread_count' => $unreadCount,
        ]);
    }
}
