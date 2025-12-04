<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Berita Acara {{ $jenis === 'sempro' ? 'Seminar Proposal' : 'Sidang Skripsi' }}</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar berita acara yang perlu ditandatangani</p>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('dosen.berita-acara.index', ['jenis' => 'sempro']) }}"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $jenis === 'sempro' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Seminar Proposal
                </a>
                <a href="{{ route('dosen.berita-acara.index', ['jenis' => 'sidang']) }}"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $jenis === 'sidang' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Sidang Skripsi
                </a>
            </nav>
        </div>

        <!-- Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Sidang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role Anda</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status TTD</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($pelaksanaans as $index => $pelaksanaan)
                                @php
                                    $myRole = $pelaksanaan->pengujiSidang->where('dosen_id', $dosen->id)->first();
                                    $roleName = $myRole ? ucwords(str_replace('_', ' ', $myRole->role)) : '-';
                                    $sudahTtd = $myRole ? $myRole->ttd_berita_acara : false;
                                    
                                    // Hitung progress TTD
                                    $totalTtd = $pelaksanaan->pengujiSidang->where('ttd_berita_acara', true)->count();
                                    $totalPenguji = $pelaksanaan->pengujiSidang->count();
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $pelaksanaans->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $pelaksanaan->pendaftaranSidang->topik->mahasiswa->user->name ?? '-' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $pelaksanaan->pendaftaranSidang->topik->mahasiswa->nim ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate">
                                            {{ $pelaksanaan->pendaftaranSidang->topik->judul ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($pelaksanaan->tanggal_sidang)->format('d M Y') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($pelaksanaan->tanggal_sidang)->format('H:i') }} WIB
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ str_starts_with($myRole->role ?? '', 'pembimbing') ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $roleName }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($sudahTtd)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Sudah TTD
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Belum TTD
                                            </span>
                                        @endif
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $totalTtd }}/{{ $totalPenguji }} sudah TTD
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('dosen.berita-acara.show', $pelaksanaan) }}" 
                                            class="text-blue-600 hover:text-blue-900 font-medium">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        Belum ada berita acara yang tersedia
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($pelaksanaans->hasPages())
                    <div class="mt-6">
                        {{ $pelaksanaans->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
