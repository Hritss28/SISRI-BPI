<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Mahasiswa -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Mahasiswa</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_mahasiswa'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Dosen -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Dosen</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_dosen'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Topik Menunggu -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Topik Menunggu</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['topik_menunggu'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Topik Diterima -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Topik Diterima</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['topik_diterima'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Periode Aktif -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Periode Aktif</h3>
                    @if($stats['periode_aktif'])
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xl font-bold text-blue-600">{{ $stats['periode_aktif']->nama }}</p>
                                <p class="text-gray-500">{{ $stats['periode_aktif']->tahun_akademik }} - Semester {{ ucfirst($stats['periode_aktif']->jenis) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Tanggal:</p>
                                <p class="text-gray-700">{{ $stats['periode_aktif']->tanggal_mulai->format('d M Y') }} - {{ $stats['periode_aktif']->tanggal_selesai->format('d M Y') }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500">Tidak ada periode aktif.</p>
                    @endif
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="{{ route('admin.mahasiswa.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Kelola Mahasiswa</h3>
                        <p class="text-gray-500">Tambah, edit, dan hapus data mahasiswa</p>
                    </div>
                </a>

                <a href="{{ route('admin.dosen.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Kelola Dosen</h3>
                        <p class="text-gray-500">Tambah, edit, dan hapus data dosen</p>
                    </div>
                </a>

                <a href="{{ route('admin.periode.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Kelola Periode</h3>
                        <p class="text-gray-500">Atur periode akademik</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
