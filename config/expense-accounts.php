<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Expense Account Mapping
    |--------------------------------------------------------------------------
    |
    | This configuration maps expense request categories to their corresponding
    | chart of accounts for automatic journal posting.
    |
    */

    'category_mapping' => [
        'tank_truck_maintenance' => [
            'account_code' => '5110',
            'account_name' => 'Beban Perawatan Kendaraan',
            'description' => 'Biaya perawatan dan perbaikan truk tangki',
        ],
        'license_fee' => [
            'account_code' => '5120',
            'account_name' => 'Beban Lisensi & Perizinan',
            'description' => 'Biaya lisensi software dan perizinan usaha',
        ],
        'business_travel' => [
            'account_code' => '5130',
            'account_name' => 'Beban Perjalanan Dinas',
            'description' => 'Biaya perjalanan dinas karyawan',
        ],
        'utilities' => [
            'account_code' => '5140',
            'account_name' => 'Beban Utilitas',
            'description' => 'Biaya listrik, air, internet, dan utilitas lainnya',
        ],
        'other' => [
            'account_code' => '5150',
            'account_name' => 'Beban Lain-lain',
            'description' => 'Beban operasional lainnya',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Cash Account
    |--------------------------------------------------------------------------
    |
    | The default cash/bank account to be credited when expense is paid.
    |
    */

    'default_cash_account' => [
        'account_code' => '1110',
        'account_name' => 'Kas & Bank',
        'description' => 'Akun kas dan bank perusahaan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Journal Settings
    |--------------------------------------------------------------------------
    |
    | Settings for automatic journal creation from expense requests.
    |
    */

    'journal_settings' => [
        'auto_post_on_approval' => false, // Create journal as draft when approved
        'auto_post_on_payment' => true,   // Post journal when marked as paid
        'journal_prefix' => 'JRN-EXP',
        'description_template' => 'Expense Request: {title}',
    ],

    /*
    |--------------------------------------------------------------------------
    | Required Accounts
    |--------------------------------------------------------------------------
    |
    | List of accounts that should be created for expense management.
    |
    */

    'required_accounts' => [
        [
            'kode_akun' => '5110',
            'nama_akun' => 'Beban Perawatan Kendaraan',
            'kategori_akun' => 'Beban',
            'tipe_akun' => 'Debit',
            'saldo_awal' => 0,
        ],
        [
            'kode_akun' => '5120',
            'nama_akun' => 'Beban Lisensi & Perizinan',
            'kategori_akun' => 'Beban',
            'tipe_akun' => 'Debit',
            'saldo_awal' => 0,
        ],
        [
            'kode_akun' => '5130',
            'nama_akun' => 'Beban Perjalanan Dinas',
            'kategori_akun' => 'Beban',
            'tipe_akun' => 'Debit',
            'saldo_awal' => 0,
        ],
        [
            'kode_akun' => '5140',
            'nama_akun' => 'Beban Utilitas',
            'kategori_akun' => 'Beban',
            'tipe_akun' => 'Debit',
            'saldo_awal' => 0,
        ],
        [
            'kode_akun' => '5150',
            'nama_akun' => 'Beban Lain-lain',
            'kategori_akun' => 'Beban',
            'tipe_akun' => 'Debit',
            'saldo_awal' => 0,
        ],
        [
            'kode_akun' => '1110',
            'nama_akun' => 'Kas & Bank',
            'kategori_akun' => 'Aset',
            'tipe_akun' => 'Debit',
            'saldo_awal' => 0,
        ],
    ],
];
