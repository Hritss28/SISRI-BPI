<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <!-- Page Title -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Topik Skripsi</h1>
            @if(!$topik)
                <a href="{{ route('mahasiswa.topik.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Ajukan Topik
                </a>
            @endif
        </div>

        @if($topik)
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-sm mb-6">
                <div class="p-6">
                    <!-- Status Badge -->
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">Status Pengajuan</h2>
                        @if($topik->status === 'menunggu')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Menunggu Persetujuan
                            </span>
                        @elseif($topik->status === 'diterima')
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Diterima
                            </span>
                        @else
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Ditolak
                            </span>
                        @endif
                    </div>

                    <!-- Topic Info -->
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $topik->judul }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Bidang Minat:</span>
                                <span class="text-gray-900 font-medium ml-2">{{ $topik->bidangMinat->nama ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Tanggal Pengajuan:</span>
                                <span class="text-gray-900 font-medium ml-2">{{ $topik->created_at->format('d M Y H:i') }}</span>
                            </div>
                            @if($topik->file_proposal)
                                <div class="md:col-span-2">
                                    <span class="text-gray-500">File Proposal:</span>
                                    <a href="{{ Storage::url($topik->file_proposal) }}" target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 ml-2">
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Download Proposal
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($topik->catatan)
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-800">
                                <strong>Catatan:</strong> {{ $topik->catatan }}
                            </p>
                        </div>
                    @endif

                    @if($topik->status === 'ditolak')
                        <div class="mt-4">
                            <a href="{{ route('mahasiswa.topik.edit', $topik) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit & Ajukan Ulang
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pembimbing Status -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Status Persetujuan Pembimbing</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($topik->usulanPembimbing as $usulan)
                            <div class="border rounded-lg p-4 {{ $usulan->status === 'diterima' ? 'bg-green-50 border-green-200' : ($usulan->status === 'ditolak' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200') }}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-gray-700">Pembimbing {{ $usulan->urutan }}</span>
                                    @if($usulan->status === 'menunggu')
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Menunggu</span>
                                    @elseif($usulan->status === 'diterima')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">✓ Diterima</span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">✗ Ditolak</span>
                                    @endif
                                </div>
                                <p class="text-gray-900 font-semibold">{{ $usulan->dosen->nama }}</p>
                                <p class="text-sm text-gray-500">{{ $usulan->dosen->nidn ?? $usulan->dosen->nip ?? '-' }}</p>
                                @if($usulan->catatan)
                                    <p class="text-sm text-gray-600 mt-2 italic">"{{ $usulan->catatan }}"</p>
                                @endif
                                @if($usulan->jangka_waktu)
                                    <p class="text-sm text-gray-500 mt-1">
                                        Jangka waktu hingga: {{ \Carbon\Carbon::parse($usulan->jangka_waktu)->format('d M Y') }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Approval Progress -->
            <div class="bg-white rounded-lg shadow-sm mt-6">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Progress Persetujuan</h2>
                    <div class="flex items-center justify-center space-x-4">
                        @php
                            $pembimbing1 = $topik->usulanPembimbing->where('urutan', 1)->first();
                            $pembimbing2 = $topik->usulanPembimbing->where('urutan', 2)->first();
                        @endphp
                        
                        <!-- Step 1: Pengajuan -->
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-xs mt-2 text-gray-600">Pengajuan</span>
                        </div>

                        <div class="flex-1 h-1 {{ $pembimbing1 && $pembimbing1->status === 'diterima' ? 'bg-green-500' : 'bg-gray-300' }}"></div>

                        <!-- Step 2: Pembimbing 1 -->
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full {{ $pembimbing1 && $pembimbing1->status === 'diterima' ? 'bg-green-500 text-white' : ($pembimbing1 && $pembimbing1->status === 'ditolak' ? 'bg-red-500 text-white' : 'bg-yellow-500 text-white') }} flex items-center justify-center">
                                @if($pembimbing1 && $pembimbing1->status === 'diterima')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @elseif($pembimbing1 && $pembimbing1->status === 'ditolak')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @endif
                            </div>
                            <span class="text-xs mt-2 text-gray-600">Pembimbing 1</span>
                        </div>

                        <div class="flex-1 h-1 {{ $pembimbing2 && $pembimbing2->status === 'diterima' ? 'bg-green-500' : 'bg-gray-300' }}"></div>

                        <!-- Step 3: Pembimbing 2 -->
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full {{ $pembimbing2 && $pembimbing2->status === 'diterima' ? 'bg-green-500 text-white' : ($pembimbing2 && $pembimbing2->status === 'ditolak' ? 'bg-red-500 text-white' : 'bg-yellow-500 text-white') }} flex items-center justify-center">
                                @if($pembimbing2 && $pembimbing2->status === 'diterima')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @elseif($pembimbing2 && $pembimbing2->status === 'ditolak')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @endif
                            </div>
                            <span class="text-xs mt-2 text-gray-600">Pembimbing 2</span>
                        </div>

                        <div class="flex-1 h-1 {{ $topik->status === 'diterima' ? 'bg-green-500' : 'bg-gray-300' }}"></div>

                        <!-- Step 4: Selesai -->
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full {{ $topik->status === 'diterima' ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-500' }} flex items-center justify-center">
                                @if($topik->status === 'diterima')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    <span class="text-sm font-bold">4</span>
                                @endif
                            </div>
                            <span class="text-xs mt-2 text-gray-600">Selesai</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-12 text-center">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Belum Ada Topik Skripsi</h3>
                    <p class="text-gray-500 mb-6">Anda belum mengajukan topik skripsi. Silakan ajukan topik terlebih dahulu untuk memulai proses bimbingan.</p>
                    <a href="{{ route('mahasiswa.topik.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-lg">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Ajukan Topik Skripsi
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
