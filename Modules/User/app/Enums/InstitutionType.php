<?php

namespace Modules\User\Enums;

enum InstitutionType: string {
    case PUSAT = 'pusat';
    case PROVINSI = 'provinsi';
    case KAB_KOTA = 'kab_kota';
    case SWASTA = 'swasta';
}
