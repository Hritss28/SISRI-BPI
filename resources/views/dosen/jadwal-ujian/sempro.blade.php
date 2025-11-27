<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Jadwal Seminar Proposal</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar jadwal seminar proposal yang melibatkan Anda sebagai penguji atau pembimbing</p>
        </div>

        <!-- Flash Message -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Dijadwalkan</p>
                        <p class="text-xl font-bold text-gray-800">{{ $jadwalSempro->where('status', 'dijadwalkan')->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Berlangsung</p>
                        <p class="text-xl font-bold text-gray-800">{{ $jadwalSempro->where('status', 'berlangsung')->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Selesai</p>
                        <p class="text-xl font-bold text-gray-800">{{ $jadwalSempro->where('status', 'selesai')->count() }}</p>
                    </div>
                </div>
            </div>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tempat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peran Anda</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($jadwalSempro as $index => $pelaksanaan)
                                @php
                                    // Determine role
                                    $isPenguji = $pelaksanaan->pengujiSidang->where('dosen_id', $dosen->id)->isNotEmpty();
                                    $pengujiRole = $isPenguji ? $pelaksanaan->pengujiSidang->where('dosen_id', $dosen->id)->first()->role : null;
                                    $isPembimbing1 = $pelaksanaan->pendaftaranSidang->topik->pembimbing1_id === $dosen->id;
                                    $isPembimbing2 = $pelaksanaan->pendaftaranSidang->topik->pembimbing2_id === $dosen->id;
                                    
                                    $roleDisplay = [];
                                    // Add penguji role if exists and not a pembimbing role (to avoid duplicate)
                                    if ($pengujiRole && !str_contains($pengujiRole, 'pembimbing')) {
                                        $roleDisplay[] = ucfirst(str_replace('_', ' ', $pengujiRole));
                                    }
                                    // Add pembimbing roles
                                    if ($isPembimbing1) $roleDisplay[] = 'Pembimbing 1';
                                    if ($isPembimbing2) $roleDisplay[] = 'Pembimbing 2';
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $jadwalSempro->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-blue-600 font-medium text-sm">
                                                        {{ substr($pelaksanaan->pendaftaranSidang->topik->mahasiswa->user->name ?? '-', 0, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $pelaksanaan->pendaftaranSidang->topik->mahasiswa->user->name ?? '-' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $pelaksanaan->pendaftaranSidang->topik->mahasiswa->nim ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $pelaksanaan->pendaftaranSidang->topik->judul ?? '-' }}">
                                            {{ Str::limit($pelaksanaan->pendaftaranSidang->topik->judul ?? '-', 50) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $pelaksanaan->tanggal_sidang ? $pelaksanaan->tanggal_sidang->format('d M Y') : '-' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $pelaksanaan->tanggal_sidang ? $pelaksanaan->tanggal_sidang->format('H:i') : '-' }} WIB
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $pelaksanaan->tempat ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @foreach($roleDisplay as $role)
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if(str_contains($role, 'Pembimbing')) bg-purple-100 text-purple-800
                                                @elseif(str_contains($role, 'ketua')) bg-blue-100 text-blue-800
                                                @else bg-gray-100 text-gray-800
                                                @endif mr-1 mb-1">
                                                {{ $role }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($pelaksanaan->status === 'dijadwalkan')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Dijadwalkan
                                            </span>
                                        @elseif($pelaksanaan->status === 'berlangsung')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Berlangsung
                                            </span>
                                        @elseif($pelaksanaan->status === 'selesai')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Selesai
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ ucfirst($pelaksanaan->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('dosen.jadwal-ujian.show', $pelaksanaan) }}" class="text-blue-600 hover:text-blue-900">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        <div class="flex flex-col items-center py-8">
                                            <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="text-gray-500">Belum ada jadwal seminar proposal</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($jadwalSempro->hasPages())
                    <div class="mt-6">
                        {{ $jadwalSempro->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
