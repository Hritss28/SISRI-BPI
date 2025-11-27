<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <!-- Page Title -->
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Mahasiswa</h1>

        @if(isset($needsProfile) && $needsProfile)
                <!-- Alert: Profil belum lengkap -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Perhatian:</strong> Data profil mahasiswa Anda belum lengkap. Hubungi Admin untuk melengkapi data Anda.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Info Mahasiswa -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-6">
                            @if($mahasiswa)
                                <h3 class="text-2xl font-bold text-gray-900">{{ $mahasiswa->nama }}</h3>
                                <p class="text-gray-500">NIM: {{ $mahasiswa->nim }}</p>
                                <p class="text-gray-500">{{ $mahasiswa->prodi->nama ?? '-' }} | Angkatan {{ $mahasiswa->angkatan }}</p>
                            @else
                                <h3 class="text-2xl font-bold text-gray-900">{{ auth()->user()->name }}</h3>
                                <p class="text-gray-500">NIM: {{ auth()->user()->username }}</p>
                                <p class="text-gray-500">Email: {{ auth()->user()->email }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Topik -->
            @if($topik)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Topik Skripsi</h3>
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-xl font-bold text-gray-800">{{ $topik->judul }}</h4>
                                @if($topik->status === 'menunggu')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">Menunggu</span>
                                @elseif($topik->status === 'diterima')
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Diterima</span>
                                @else
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">Ditolak</span>
                                @endif
                            </div>
                            <p class="text-gray-600 mb-2"><strong>Bidang Minat:</strong> {{ $topik->bidangMinat->nama ?? '-' }}</p>
                            
                            <div class="mt-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">Pembimbing:</p>
                                @foreach($topik->usulanPembimbing as $usulan)
                                    <div class="flex items-center justify-between py-2 border-b last:border-b-0">
                                        <span>Pembimbing {{ $usulan->urutan }}: {{ $usulan->dosen->nama }}</span>
                                        @if($usulan->status === 'menunggu')
                                            <span class="text-yellow-600 text-sm">Menunggu</span>
                                        @elseif($usulan->status === 'diterima')
                                            <span class="text-green-600 text-sm">✓ Diterima</span>
                                        @else
                                            <span class="text-red-600 text-sm">✗ Ditolak</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if($topik->catatan)
                                <div class="mt-4 p-3 bg-gray-50 rounded">
                                    <p class="text-sm text-gray-600"><strong>Catatan:</strong> {{ $topik->catatan }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Topik Skripsi</h3>
                        <p class="text-gray-500 mb-4">Anda belum mengajukan topik skripsi. Silakan ajukan topik terlebih dahulu.</p>
                        <a href="{{ route('mahasiswa.topik.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Ajukan Topik
                        </a>
                    </div>
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Bimbingan Proposal</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['bimbingan_proposal'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Bimbingan Skripsi</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['bimbingan_skripsi'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Menunggu Respon</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['bimbingan_menunggu'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pendaftaran Sidang</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['pendaftaran_sidang'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bimbingan -->
            @if($recentBimbingan->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Bimbingan Terbaru</h3>
                        <div class="space-y-4">
                            @foreach($recentBimbingan as $bimbingan)
                                <div class="border rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $bimbingan->pokok_bimbingan }}</p>
                                            <p class="text-sm text-gray-500">{{ $bimbingan->dosen->nama }} | {{ ucfirst($bimbingan->jenis) }}</p>
                                        </div>
                                        @if($bimbingan->status === 'menunggu')
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Menunggu</span>
                                        @elseif($bimbingan->status === 'disetujui')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Disetujui</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Direvisi</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
    </div>
</x-app-layout>
