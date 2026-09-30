<?php

namespace Modules\Dashboard\Listeners;

use Modules\Dashboard\Services\NotificationService;
use Modules\Project\Events\ProjectApprovalDecided;
use Modules\Project\Enums\ProjectType;

/**
 * Mengubah kejadian "keputusan proyek daerah" menjadi notifikasi in-app.
 */
class SendProjectNotification
{
    public function __construct(
        private NotificationService $notifications
    ) {}

    public function handle(ProjectApprovalDecided $event): void
    {
        // Hanya proyek daerah (Kab/Kota & Provinsi) yang memberi
        // notifikasi ke navbar Admin Kab/Kota & Admin Provinsi.
        $isDaerahProject = in_array($event->projectType, [
            ProjectType::PROVINSI->value,
            ProjectType::KAB_KOTA->value,
        ], true);

        if (! $isDaerahProject) {
            return;
        }

        // 1. Diterima oleh Pusat -> Status berubah menjadi 'Menunggu Tim'
        $approvedByPusat = $event->newStatus === 'Menunggu Tim'
            && in_array($event->previousStatus, ['Draft', null], true);

        // 2. Tim Selesai Ditentukan -> Status berubah menjadi 'On Progress'
        $teamAssigned = $event->newStatus === 'On Progress'
            && $event->previousStatus === 'Menunggu Tim';

        // 3. Proyek Selesai -> Status 'Completed'
        $completed = $event->newStatus === 'Completed';

        // 4. Proyek Kedaluwarsa -> Status 'Kedaluwarsa'
        $expired = $event->newStatus === 'Kedaluwarsa';

        // Jika tidak memenuhi kriteria status di atas, abaikan
        if (! $approvedByPusat && ! $teamAssigned && ! $completed && ! $expired) {
            return;
        }

        $this->notifications->projectApproved(
            projectId: $event->projectId,
            projectName: $event->projectName,
            projectType: $event->projectType,
            creatorId: $event->creatorId,
            previousStatus: (string) $event->previousStatus,
            newStatus: $event->newStatus,
            actorId: $event->actorId,
        );
    }
}
