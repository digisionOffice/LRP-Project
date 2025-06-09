<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Karyawan;
use App\Models\Jabatan;
use App\Models\Divisi;
use App\Models\Entitas;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing jabatan and divisi IDs
        $jabatanIds = Jabatan::pluck('id')->toArray();
        $divisiIds = Divisi::pluck('id')->toArray();

        $karyawanData = [
            [
                'no_induk' => 'EMP001',
                'nama' => 'Budi Santoso',
                'hp' => '081234567890',
                'email' => 'budi.santoso@lrp.com',
                'id_jabatan' => $jabatanIds[0], // Direktur
                'id_divisi' => $divisiIds[0], // Direksi
            ],
            [
                'no_induk' => 'EMP002',
                'nama' => 'Siti Nurhaliza',
                'hp' => '081234567891',
                'email' => 'siti.nurhaliza@lrp.com',
                'id_jabatan' => $jabatanIds[1], // Manager
                'id_divisi' => $divisiIds[1], // Sales
            ],
            [
                'no_induk' => 'EMP003',
                'nama' => 'Ahmad Fauzi',
                'hp' => '081234567892',
                'email' => 'ahmad.fauzi@lrp.com',
                'id_jabatan' => $jabatanIds[1], // Manager
                'id_divisi' => $divisiIds[2], // Operasional
            ],
            [
                'no_induk' => 'EMP004',
                'nama' => 'Dewi Sartika',
                'hp' => '081234567893',
                'email' => 'dewi.sartika@lrp.com',
                'id_jabatan' => $jabatanIds[1], // Manager
                'id_divisi' => $divisiIds[4], // Keuangan
            ],
            [
                'no_induk' => 'EMP005',
                'nama' => 'Rudi Hermawan',
                'hp' => '081234567894',
                'email' => 'rudi.hermawan@lrp.com',
                'id_jabatan' => $jabatanIds[2], // Supervisor
                'id_divisi' => $divisiIds[2], // Operasional
            ],
            [
                'no_induk' => 'EMP006',
                'nama' => 'Maya Sari',
                'hp' => '081234567895',
                'email' => 'maya.sari@lrp.com',
                'id_jabatan' => $jabatanIds[3], // Staff
                'id_divisi' => $divisiIds[1], // Sales
            ],
            [
                'no_induk' => 'EMP007',
                'nama' => 'Joko Widodo',
                'hp' => '081234567896',
                'email' => 'joko.widodo@lrp.com',
                'id_jabatan' => $jabatanIds[4], // Driver
                'id_divisi' => $divisiIds[2], // Operasional
            ],
            [
                'no_induk' => 'EMP008',
                'nama' => 'Rina Susanti',
                'hp' => '081234567897',
                'email' => 'rina.susanti@lrp.com',
                'id_jabatan' => $jabatanIds[5], // Admin
                'id_divisi' => $divisiIds[3], // Administrasi
            ],
            [
                'no_induk' => 'EMP009',
                'nama' => 'Bambang Sutrisno',
                'hp' => '081234567898',
                'email' => 'bambang.sutrisno@lrp.com',
                'id_jabatan' => $jabatanIds[4], // Driver
                'id_divisi' => $divisiIds[2], // Operasional
            ],
            [
                'no_induk' => 'EMP010',
                'nama' => 'Lestari Wulandari',
                'hp' => '081234567899',
                'email' => 'lestari.wulandari@lrp.com',
                'id_jabatan' => $jabatanIds[3], // Staff
                'id_divisi' => $divisiIds[4], // Keuangan
            ],
            [
                'no_induk' => 'EMP011',
                'nama' => 'Agus Salim',
                'hp' => '081234567800',
                'email' => 'agus.salim@lrp.com',
                'id_jabatan' => $jabatanIds[4], // Driver
                'id_divisi' => $divisiIds[2], // Operasional
            ],
            [
                'no_induk' => 'EMP012',
                'nama' => 'Fitri Handayani',
                'hp' => '081234567801',
                'email' => 'fitri.handayani@lrp.com',
                'id_jabatan' => $jabatanIds[6], // Operator
                'id_divisi' => $divisiIds[6], // IT
            ],
            [
                'no_induk' => 'EMP013',
                'nama' => 'Hendra Gunawan',
                'hp' => '081234567802',
                'email' => 'hendra.gunawan@lrp.com',
                'id_jabatan' => $jabatanIds[4], // Driver
                'id_divisi' => $divisiIds[2], // Operasional
            ],
            [
                'no_induk' => 'EMP014',
                'nama' => 'Indira Sari',
                'hp' => '081234567803',
                'email' => 'indira.sari@lrp.com',
                'id_jabatan' => $jabatanIds[3], // Staff
                'id_divisi' => $divisiIds[5], // HRD
            ],
            [
                'no_induk' => 'EMP015',
                'nama' => 'Wahyu Pratama',
                'hp' => '081234567804',
                'email' => 'wahyu.pratama@lrp.com',
                'id_jabatan' => $jabatanIds[4], // Driver
                'id_divisi' => $divisiIds[2], // Operasional
            ],
        ];

        foreach ($karyawanData as $karyawan) {
            Karyawan::create($karyawan);
        }
    }
}
