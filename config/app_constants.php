<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Roles
    |--------------------------------------------------------------------------
    |
    | Define all user roles used throughout the application
    |
    */
    'user_roles' => [
        'admin' => 'Admin',
        'supervisor' => 'Supervisor',
        'karyawan' => 'Karyawan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Attendance Status
    |--------------------------------------------------------------------------
    |
    | Define all attendance status options
    |
    */
    'attendance_status' => [
        'hadir' => 'Hadir',
        'terlambat' => 'Terlambat',
        'izin' => 'Izin',
        'sakit' => 'Sakit',
        'cuti' => 'Cuti',
        'alpha' => 'Alpha (Tanpa Keterangan)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Geofencing Settings
    |--------------------------------------------------------------------------
    |
    | Default geofencing settings
    |
    */
    'geofencing' => [
        'default_radius' => 100, // meters
        'max_radius' => 1000, // meters
        'enable_by_default' => true,
    ],
];
