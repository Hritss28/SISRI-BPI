<x-app-layout>
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('dosen.berita-acara.index', ['jenis' => $jenis]) }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Berita Acara {{ $jenis === 'sempro' ? 'Seminar Proposal' : 'Sidang Skripsi' }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $pelaksanaan->pendaftaranSidang->topik->mahasiswa->user->name ?? '-' }} - 
                {{ $pelaksanaan->pendaftaranSidang->topik->mahasiswa->nim ?? '-' }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Info Berita Acara -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Data Mahasiswa -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Data Mahasiswa</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nama</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $pelaksanaan->pendaftaranSidang->topik->mahasiswa->user->name ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">NIM</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $pelaksanaan->pendaftaranSidang->topik->mahasiswa->nim ?? '-' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Judul {{ $jenis === 'sempro' ? 'Proposal' : 'Skripsi' }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $pelaksanaan->pendaftaranSidang->topik->judul ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Info Sidang -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Sidang</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tanggal & Waktu</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($pelaksanaan->tanggal_sidang)->format('l, d F Y') }}<br>
                                    {{ \Carbon\Carbon::parse($pelaksanaan->tanggal_sidang)->format('H:i') }} WIB
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tempat</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $pelaksanaan->tempat }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Tim Pembimbing & Penguji dengan Status TTD -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Status Tanda Tangan Berita Acara</h3>
                        
                        <!-- Progress Bar -->
                        <div class="mb-6">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Progress TTD</span>
                                <span>{{ $totalTtd }}/{{ $totalPenguji }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-green-500 h-2.5 rounded-full transition-all duration-300" 
                                    style="width: {{ $totalPenguji > 0 ? ($totalTtd / $totalPenguji) * 100 : 0 }}%"></div>
                            </div>
                            @if($semuaSudahTtd)
                                <p class="text-sm text-green-600 mt-2 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Semua dosen sudah menandatangani berita acara
                                </p>
                            @endif
                        </div>

                        <!-- Tim Pembimbing -->
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Tim Pembimbing</h4>
                        <div class="space-y-2 mb-6">
                            @foreach($pembimbingList as $pembimbing)
                                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-medium mr-3 text-sm">
                                            {{ substr($pembimbing->role, -1) }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $pembimbing->dosen->user->name ?? '-' }}</p>
                                            <p class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $pembimbing->role)) }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if($pembimbing->ttd_berita_acara)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Sudah TTD
                                            </span>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $pembimbing->tanggal_ttd ? $pembimbing->tanggal_ttd->format('d M Y H:i') : '' }}
                                            </p>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Belum TTD
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Tim Penguji -->
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Tim Penguji</h4>
                        <div class="space-y-2">
                            @foreach($pengujiList as $pnguji)
                                <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-800 font-medium mr-3 text-sm">
                                            {{ substr($pnguji->role, -1) }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $pnguji->dosen->user->name ?? '-' }}</p>
                                            <p class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $pnguji->role)) }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if($pnguji->ttd_berita_acara)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Sudah TTD
                                            </span>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $pnguji->tanggal_ttd ? $pnguji->tanggal_ttd->format('d M Y H:i') : '' }}
                                            </p>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Belum TTD
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aksi TTD -->
            <div class="space-y-6">
                <!-- Status Anda -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Status Anda</h3>
                        <div class="text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ str_starts_with($penguji->role, 'pembimbing') ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ ucwords(str_replace('_', ' ', $penguji->role)) }}
                            </span>
                            
                            <div class="mt-4">
                                @if($penguji->ttd_berita_acara)
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-3">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-green-700">Anda sudah menandatangani</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $penguji->tanggal_ttd ? $penguji->tanggal_ttd->format('d M Y H:i') : '' }}
                                    </p>
                                @else
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 mb-3">
                                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-yellow-700">Belum ditandatangani</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-3">
                        @if(!$penguji->ttd_berita_acara)
                            <form action="{{ route('dosen.berita-acara.tanda-tangan', $pelaksanaan) }}" method="POST" id="ttd-form">
                                @csrf
                                <button type="button" 
                                    onclick="confirmAction('ttd-form', 'Tanda Tangan Berita Acara', 'Apakah Anda yakin ingin menandatangani berita acara ini? Tindakan ini tidak dapat dibatalkan.', 'Ya, Tanda Tangan')"
                                    class="w-full inline-flex justify-center items-center px-4 py-3 bg-green-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 transition">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                    Tanda Tangan Berita Acara
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('dosen.berita-acara.download-pdf', $pelaksanaan) }}" 
                            class="w-full inline-flex justify-center items-center px-4 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download PDF
                        </a>
                    </div>
                </div>

                <!-- Info -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-yellow-700">
                            <p class="font-medium">Catatan:</p>
                            <ul class="list-disc list-inside mt-1 text-xs">
                                <li>Berita acara harus ditandatangani oleh semua pembimbing dan penguji</li>
                                <li>Tanda tangan tidak dapat dibatalkan setelah dikonfirmasi</li>
                                <li>PDF dapat diunduh setelah semua pihak menandatangani</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
