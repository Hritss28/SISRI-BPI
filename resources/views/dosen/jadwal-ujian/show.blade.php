<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center mb-2">
                <a href="{{ $pelaksanaan->pendaftaranSidang->jenis === 'seminar_proposal' ? route('dosen.jadwal-ujian.sempro') : route('dosen.jadwal-ujian.sidang') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Detail Jadwal Ujian</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $pelaksanaan->pendaftaranSidang->jenis === 'seminar_proposal' ? 'Seminar Proposal' : 'Sidang Skripsi' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Jadwal Info Card -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600">
                        <h2 class="text-lg font-semibold text-white">Informasi Jadwal</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="flex items-center mb-4">
                                    <div class="p-3 bg-blue-100 rounded-lg">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm text-gray-500">Tanggal</p>
                                        <p class="text-lg font-semibold text-gray-900">
                                            {{ $pelaksanaan->tanggal_sidang ? $pelaksanaan->tanggal_sidang->format('l, d F Y') : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <div class="p-3 bg-green-100 rounded-lg">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm text-gray-500">Waktu</p>
                                        <p class="text-lg font-semibold text-gray-900">
                                            {{ $pelaksanaan->tanggal_sidang ? $pelaksanaan->tanggal_sidang->format('H:i') : '-' }} WIB
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center mb-4">
                                    <div class="p-3 bg-purple-100 rounded-lg">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm text-gray-500">Tempat</p>
                                        <p class="text-lg font-semibold text-gray-900">
                                            {{ $pelaksanaan->tempat ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <div class="p-3 bg-yellow-100 rounded-lg">
                                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm text-gray-500">Status</p>
                                        <p class="text-lg font-semibold">
                                            @if($pelaksanaan->status === 'dijadwalkan')
                                                <span class="text-yellow-600">Dijadwalkan</span>
                                            @elseif($pelaksanaan->status === 'berlangsung')
                                                <span class="text-blue-600">Berlangsung</span>
                                            @elseif($pelaksanaan->status === 'selesai')
                                                <span class="text-green-600">Selesai</span>
                                            @else
                                                <span class="text-gray-600">{{ ucfirst($pelaksanaan->status) }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mahasiswa Info -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Informasi Mahasiswa</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-blue-600 font-bold text-xl">
                                    {{ substr($pelaksanaan->pendaftaranSidang->topik->mahasiswa->user->name ?? '-', 0, 2) }}
                                </span>
                            </div>
                            <div class="ml-6">
                                <h3 class="text-xl font-semibold text-gray-900">
                                    {{ $pelaksanaan->pendaftaranSidang->topik->mahasiswa->user->name ?? '-' }}
                                </h3>
                                <p class="text-gray-500">{{ $pelaksanaan->pendaftaranSidang->topik->mahasiswa->nim ?? '-' }}</p>
                                <div class="mt-3">
                                    <p class="text-sm text-gray-500">Judul Skripsi:</p>
                                    <p class="text-gray-900 font-medium">{{ $pelaksanaan->pendaftaranSidang->topik->judul ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tim Penguji -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Tim Penguji</h2>
                    </div>
                    <div class="p-6">
                        @php
                            // Filter penguji yang bukan pembimbing
                            $pembimbing1Id = $pelaksanaan->pendaftaranSidang->topik->pembimbing1_id;
                            $pembimbing2Id = $pelaksanaan->pendaftaranSidang->topik->pembimbing2_id;
                            $timPenguji = $pelaksanaan->pengujiSidang->filter(function($penguji) use ($pembimbing1Id, $pembimbing2Id) {
                                return $penguji->dosen_id !== $pembimbing1Id && $penguji->dosen_id !== $pembimbing2Id;
                            });
                        @endphp
                        <div class="space-y-4">
                            @forelse($timPenguji as $penguji)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-indigo-600 font-medium text-sm">
                                                {{ substr($penguji->dosen->user->name ?? '-', 0, 2) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-900">{{ $penguji->dosen->user->name ?? '-' }}</p>
                                            <p class="text-sm text-gray-500">{{ $penguji->dosen->nip ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                        @if($penguji->role === 'ketua_penguji') bg-blue-100 text-blue-800
                                        @elseif($penguji->role === 'sekretaris') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $penguji->role)) }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">Belum ada penguji yang ditugaskan</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Your Role -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Peran Anda</h2>
                    </div>
                    <div class="p-6">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full 
                                @if(str_contains($role ?? '', 'Pembimbing')) bg-purple-100
                                @elseif(str_contains($role ?? '', 'ketua')) bg-blue-100
                                @else bg-gray-100
                                @endif mb-4">
                                <svg class="w-8 h-8 
                                    @if(str_contains($role ?? '', 'Pembimbing')) text-purple-600
                                    @elseif(str_contains($role ?? '', 'ketua')) text-blue-600
                                    @else text-gray-600
                                    @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <p class="text-xl font-semibold text-gray-900">{{ $role ?? '-' }}</p>
                            <p class="text-sm text-gray-500 mt-1">dalam ujian ini</p>
                        </div>
                    </div>
                </div>

                <!-- Pembimbing -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Dosen Pembimbing</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <p class="text-xs text-purple-600 font-medium mb-1">Pembimbing 1</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $pelaksanaan->pendaftaranSidang->topik->usulanPembimbing1?->dosen?->user?->name ?? '-' }}
                            </p>
                        </div>
                        @if($pelaksanaan->pendaftaranSidang->topik->usulanPembimbing2)
                            <div class="p-3 bg-purple-50 rounded-lg">
                                <p class="text-xs text-purple-600 font-medium mb-1">Pembimbing 2</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $pelaksanaan->pendaftaranSidang->topik->usulanPembimbing2?->dosen?->user?->name ?? '-' }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Periode Info -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Periode</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-500">Periode Akademik</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $pelaksanaan->pendaftaranSidang->jadwalSidang->periode->nama ?? '-' }}
                        </p>
                    </div>
                </div>

                <!-- Quick Actions -->
                @if($pelaksanaan->status === 'dijadwalkan' || $pelaksanaan->status === 'berlangsung')
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-800">Aksi Cepat</h2>
                        </div>
                        <div class="p-6 space-y-3">
                            @php
                                $isPenguji = $pelaksanaan->pengujiSidang->where('dosen_id', $dosen->id)->isNotEmpty();
                            @endphp
                            @if($isPenguji && $pelaksanaan->pendaftaranSidang->jenis === 'seminar_proposal')
                                <a href="{{ route('dosen.nilai-sempro.create', $pelaksanaan) }}" 
                                   class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Beri Nilai Sempro
                                </a>
                            @elseif($isPenguji && $pelaksanaan->pendaftaranSidang->jenis === 'sidang_skripsi')
                                <a href="{{ route('dosen.nilai-sidang.create', $pelaksanaan) }}" 
                                   class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Beri Nilai Sidang
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
