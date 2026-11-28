<?php

namespace Modules\LMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\BimtekEventSession;

class NearestSessionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $events = BimtekEvent::with('sessions')
            ->whereIn('status', ['ongoing', 'open_registration'])
            ->get();

        $createdCount = 0;

        foreach ($events as $event) {
            $hasUpcoming = $event->sessions->contains(function ($s) use ($now) {
                $endRef = $s->end_time ?? $s->start_time;
                return $endRef && $endRef->gte($now);
            });

            if ($hasUpcoming) {
                continue;
            }

            // Tentukan tanggal sesi mendatang yang realistis
            $sessionDate = ($event->start_date && $event->start_date->gt($now))
                ? $event->start_date->copy()
                : $now->copy()->addDay();

            $startTime = $sessionDate->copy()->setTime(9, 0, 0);
            $endTime = $sessionDate->copy()->setTime(11, 0, 0);
            $nextNumber = ((int) $event->sessions->max('session_number')) + 1;

            BimtekEventSession::create([
                'bimtek_event_id'     => $event->id,
                'session_number'      => $nextNumber,
                'title'               => 'Sesi Live Zoom: Pendalaman Materi & Diskusi Interaktif',
                'session_date'        => $sessionDate->toDateString(),
                'start_time'          => $startTime,
                'end_time'            => $endTime,
                'zoom_url'            => $event->zoom_url ?: 'https://zoom.us/j/98765432100',
                'zoom_meeting_id'     => $event->zoom_meeting_id ?: '987 6543 2100',
                'zoom_passcode'       => $event->zoom_passcode ?: 'SIRENATA',
                'attendance_start_at' => $startTime->copy()->subMinutes(15),
                'attendance_end_at'   => $endTime->copy()->addMinutes(30),
            ]);

            $createdCount++;
        }

        $this->command?->info("NearestSessionDemoSeeder selesai: {$createdCount} sesi Live Zoom mendatang ditambahkan.");
    }
}
