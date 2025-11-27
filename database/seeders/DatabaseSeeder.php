<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\KoordinatorProdi;
use App\Models\BidangMinat;
use App\Models\Periode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Roles
        $this->createRoles();
        
        // Create Units (Fakultas, Jurusan, Prodi)
        $units = $this->createUnits();
        
        // Create Users and Related Data
        $this->createAdminUser();
        $this->createMahasiswaUsers($units);
        $this->createDosenUsers($units);
        $this->createKoordinatorUser($units);
        
        // Create Bidang Minat
        $this->createBidangMinat($units);
        
        // Create Active Periode
        $this->createPeriode();
    }

    private function createRoles(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'mahasiswa']);
        Role::create(['name' => 'dosen']);
        Role::create(['name' => 'koordinator']);
    }

    private function createUnits(): array
    {
        // Fakultas
        $fakultas = Unit::create([
            'nama' => 'Fakultas Teknik',
            'kode' => 'FT',
            'type' => 'fakultas',
            'parent_id' => null,
        ]);

        // Jurusan
        $jurusan = Unit::create([
            'nama' => 'Jurusan Teknik Informatika',
            'kode' => 'JTI',
            'type' => 'jurusan',
            'parent_id' => $fakultas->id,
        ]);

        // Prodi
        $prodi = Unit::create([
            'nama' => 'Program Studi Teknik Informatika',
            'kode' => 'TI',
            'type' => 'prodi',
            'parent_id' => $jurusan->id,
        ]);

        $prodi2 = Unit::create([
            'nama' => 'Program Studi Sistem Informasi',
            'kode' => 'SI',
            'type' => 'prodi',
            'parent_id' => $jurusan->id,
        ]);

        return [
            'fakultas' => $fakultas,
            'jurusan' => $jurusan,
            'prodi' => $prodi,
            'prodi2' => $prodi2,
        ];
    }

    private function createAdminUser(): void
    {
        $user = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@sisri.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        $user->assignRole('admin');
    }

    private function createMahasiswaUsers(array $units): void
    {
        // Mahasiswa 1
        $user1 = User::create([
            'name' => 'Budi Santoso',
            'username' => '2021001',
            'email' => 'budi@sisri.test',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'is_active' => true,
        ]);
        $user1->assignRole('mahasiswa');

        Mahasiswa::create([
            'user_id' => $user1->id,
            'nim' => '2021001',
            'nama' => 'Budi Santoso',
            'prodi_id' => $units['prodi']->id,
            'angkatan' => '2021',
            'no_hp' => '081234567890',
        ]);

        // Mahasiswa 2
        $user2 = User::create([
            'name' => 'Siti Rahayu',
            'username' => '2021002',
            'email' => 'siti@sisri.test',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'is_active' => true,
        ]);
        $user2->assignRole('mahasiswa');

        Mahasiswa::create([
            'user_id' => $user2->id,
            'nim' => '2021002',
            'nama' => 'Siti Rahayu',
            'prodi_id' => $units['prodi']->id,
            'angkatan' => '2021',
            'no_hp' => '081234567891',
        ]);

        // Mahasiswa 3
        $user3 = User::create([
            'name' => 'Ahmad Fauzi',
            'username' => '2020001',
            'email' => 'ahmad@sisri.test',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'is_active' => true,
        ]);
        $user3->assignRole('mahasiswa');

        Mahasiswa::create([
            'user_id' => $user3->id,
            'nim' => '2020001',
            'nama' => 'Ahmad Fauzi',
            'prodi_id' => $units['prodi2']->id,
            'angkatan' => '2020',
            'no_hp' => '081234567892',
        ]);
    }

    private function createDosenUsers(array $units): void
    {
        // Dosen 1
        $user1 = User::create([
            'name' => 'Dr. Agus Wijaya, M.Kom.',
            'username' => '197001011995121001',
            'email' => 'agus@sisri.test',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'is_active' => true,
        ]);
        $user1->assignRole('dosen');

        Dosen::create([
            'user_id' => $user1->id,
            'nip' => '197001011995121001',
            'nidn' => '0001017001',
            'nama' => 'Dr. Agus Wijaya, M.Kom.',
            'prodi_id' => $units['prodi']->id,
            'no_hp' => '081111111111',
            'email' => 'agus@sisri.test',
        ]);

        // Dosen 2
        $user2 = User::create([
            'name' => 'Dr. Dewi Lestari, M.T.',
            'username' => '198005152005012001',
            'email' => 'dewi@sisri.test',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'is_active' => true,
        ]);
        $user2->assignRole('dosen');

        Dosen::create([
            'user_id' => $user2->id,
            'nip' => '198005152005012001',
            'nidn' => '0015058001',
            'nama' => 'Dr. Dewi Lestari, M.T.',
            'prodi_id' => $units['prodi']->id,
            'no_hp' => '081222222222',
            'email' => 'dewi@sisri.test',
        ]);

        // Dosen 3
        $user3 = User::create([
            'name' => 'Ir. Hendra Kusuma, M.Cs.',
            'username' => '196808081994031002',
            'email' => 'hendra@sisri.test',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'is_active' => true,
        ]);
        $user3->assignRole('dosen');

        Dosen::create([
            'user_id' => $user3->id,
            'nip' => '196808081994031002',
            'nidn' => '0008086801',
            'nama' => 'Ir. Hendra Kusuma, M.Cs.',
            'prodi_id' => $units['prodi2']->id,
            'no_hp' => '081333333333',
            'email' => 'hendra@sisri.test',
        ]);
    }

    private function createKoordinatorUser(array $units): void
    {
        $user = User::create([
            'name' => 'Dr. Rina Marlina, M.Kom.',
            'username' => '197503201999032001',
            'email' => 'rina@sisri.test',
            'password' => Hash::make('password'),
            'role' => 'koordinator',
            'is_active' => true,
        ]);
        $user->assignRole('koordinator');

        // Create Dosen record for Koordinator
        $dosen = Dosen::create([
            'user_id' => $user->id,
            'nip' => '197503201999032001',
            'nidn' => '0020037501',
            'nama' => 'Dr. Rina Marlina, M.Kom.',
            'prodi_id' => $units['prodi']->id,
            'no_hp' => '081444444444',
            'email' => 'rina@sisri.test',
        ]);

        KoordinatorProdi::create([
            'dosen_id' => $dosen->id,
            'prodi_id' => $units['prodi']->id,
            'tahun_mulai' => 2023,
            'tahun_selesai' => null,
            'is_active' => true,
        ]);
    }

    private function createBidangMinat(array $units): void
    {
        $bidangMinat = [
            ['nama' => 'Artificial Intelligence', 'deskripsi' => 'Penelitian tentang kecerdasan buatan, machine learning, dan deep learning'],
            ['nama' => 'Data Science', 'deskripsi' => 'Analisis data, big data, dan statistik komputasi'],
            ['nama' => 'Software Engineering', 'deskripsi' => 'Rekayasa perangkat lunak, pengembangan aplikasi, dan metodologi pengembangan'],
            ['nama' => 'Computer Networks', 'deskripsi' => 'Jaringan komputer, keamanan jaringan, dan cloud computing'],
            ['nama' => 'Internet of Things', 'deskripsi' => 'Sistem embedded, sensor, dan konektivitas perangkat'],
            ['nama' => 'Web Development', 'deskripsi' => 'Pengembangan aplikasi web, frontend, dan backend'],
            ['nama' => 'Mobile Development', 'deskripsi' => 'Pengembangan aplikasi mobile Android dan iOS'],
            ['nama' => 'Database Systems', 'deskripsi' => 'Sistem basis data, data warehousing, dan optimasi query'],
        ];

        foreach ($bidangMinat as $bm) {
            BidangMinat::create([
                'nama' => $bm['nama'],
                'deskripsi' => $bm['deskripsi'],
                'prodi_id' => $units['prodi']->id,
            ]);
        }
    }

    private function createPeriode(): void
    {
        Periode::create([
            'nama' => 'Semester Ganjil 2024/2025',
            'tahun_akademik' => '2024/2025',
            'jenis' => 'ganjil',
            'tanggal_mulai' => '2024-09-01',
            'tanggal_selesai' => '2025-02-28',
            'is_active' => true,
        ]);

        Periode::create([
            'nama' => 'Semester Genap 2024/2025',
            'tahun_akademik' => '2024/2025',
            'jenis' => 'genap',
            'tanggal_mulai' => '2025-03-01',
            'tanggal_selesai' => '2025-08-31',
            'is_active' => false,
        ]);
    }
}
