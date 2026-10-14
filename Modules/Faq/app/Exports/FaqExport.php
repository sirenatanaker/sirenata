<?php

namespace Modules\Faq\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Modules\Faq\Models\Faq;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FaqExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize,
    WithChunkReading
{
    private int $no = 0;

    public function __construct(
        private ?string $search = null,
        private ?string $role = null,
    ) {}

    public function query()
    {
        $query = Faq::with(['creator', 'roles'])->latest();

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('question', 'like', $searchTerm)
                    ->orWhere('answer', 'like', $searchTerm);
            });
        }

        if ($this->role) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $this->role));
        }

        return $query;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function title(): string
    {
        return 'FAQ';
    }

    public function headings(): array
    {
        return [
            'No',
            'Pertanyaan',
            'Jawaban',
            'Ditujukan Untuk',
            'Pembuat',
        ];
    }

    public function map($row): array
    {
        $this->no++;

        return [
            $this->no,
            $row->question,
            strip_tags($row->answer),
            $row->roles->pluck('name')->implode(', '),
            $row->creator?->profile?->full_name ?? $row->creator?->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
