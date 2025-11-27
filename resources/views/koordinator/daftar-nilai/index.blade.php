<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Daftar Nilai {{ $jenis === 'sempro' ? 'Seminar Proposal' : 'Sidang Skripsi' }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Daftar nilai mahasiswa yang telah melaksanakan {{ $jenis === 'sempro' ? 'seminar proposal' : 'sidang skripsi' }}</p>
            </div>
            <!-- Tab Navigation -->
            <div class="flex space-x-2">
                <a href="{{ route('koordinator.daftar-nilai.index', ['jenis' => 'sempro']) }}"
                    class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium {{ $jenis === 'sempro' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Sempro
                </a>
                <a href="{{ route('koordinator.daftar-nilai.index', ['jenis' => 'sidang']) }}"
                    class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium {{ $jenis === 'sidang' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Sidang
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($pelaksanaans->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul {{ $jenis === 'sempro' ? 'Proposal' : 'Skripsi' }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Rata-rata</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($pelaksanaans as $index => $pelaksanaan)
                                        @php
                                            $nilaiUjian = $pelaksanaan->nilai->where('jenis_nilai', 'ujian');
                                            $nilaiRata = $nilaiUjian->count() > 0 ? $nilaiUjian->avg('nilai') : null;
                                            $isLulus = $nilaiRata !== null && $nilaiRata >= 55;
                                        @endphp
                                        <tr>
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
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($pelaksanaan->tanggal_sidang)->format('d M Y, H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($nilaiRata !== null)
                                                    <span class="text-lg font-bold {{ $isLulus ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ number_format($nilaiRata, 1) }}
                                                    </span>
                                                @else
                                                    <span class="text-sm text-gray-400">Belum dinilai</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($nilaiRata !== null)
                                                    @if($isLulus)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Lulus
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            Tidak Lulus
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        Menunggu
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('koordinator.daftar-nilai.show', $pelaksanaan) }}" 
                                                    class="text-blue-600 hover:text-blue-900">Detail</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $pelaksanaans->appends(['jenis' => $jenis])->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada {{ $jenis === 'sempro' ? 'seminar proposal' : 'sidang skripsi' }} yang selesai</h3>
                            <p class="mt-1 text-sm text-gray-500">Daftar nilai akan muncul setelah {{ $jenis === 'sempro' ? 'seminar proposal' : 'sidang skripsi' }} dilaksanakan dan dinilai.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
