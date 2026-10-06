<?php

use Illuminate\Support\Facades\Schema;

test('SIAPKerja tokens are stored in text columns', function () {
    expect(Schema::getColumnType('users', 'siapkerja_token'))->toBe('text')
        ->and(Schema::getColumnType('users', 'siapkerja_refresh_token'))->toBe('text');
});
