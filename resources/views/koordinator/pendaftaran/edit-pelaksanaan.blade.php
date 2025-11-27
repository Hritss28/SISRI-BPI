<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Jadwal Pelaksanaan
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $pendaftaran->topik->mahasiswa->user->name ?? '-' }} - {{ $pendaftaran->topik->mahasiswa->nim ?? '-' }}
                </p>
            </div>
            <a href="{{ route('koordinator.pendaftaran.index', ['jenis' => $jenis]) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Info Mahasiswa -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Mahasiswa</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pendaftaran->topik->mahasiswa->user->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">NIM</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pendaftaran->topik->mahasiswa->nim ?? '-' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Judul {{ $jenis === 'sempro' ? 'Proposal' : 'Skripsi' }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pendaftaran->topik->judul ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Form Edit -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Jadwal</h3>

                    <form action="{{ route('koordinator.pendaftaran.update-pelaksanaan', $pendaftaran) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="tanggal_sidang" class="block text-sm font-medium text-gray-700">Tanggal & Waktu</label>
                                    <input type="datetime-local" name="tanggal_sidang" id="tanggal_sidang" 
                                        value="{{ old('tanggal_sidang', $pendaftaran->pelaksanaanSidang->tanggal_sidang ? \Carbon\Carbon::parse($pendaftaran->pelaksanaanSidang->tanggal_sidang)->format('Y-m-d\TH:i') : '') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    @error('tanggal_sidang')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="tempat" class="block text-sm font-medium text-gray-700">Tempat / Ruangan</label>
                                    <input type="text" name="tempat" id="tempat" 
                                        value="{{ old('tempat', $pendaftaran->pelaksanaanSidang->tempat) }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    @error('tempat')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tim Pembimbing (Read Only) -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Pembimbing (Otomatis sebagai Penguji)</h4>
                                @foreach($pendaftaran->topik->usulanPembimbing as $p)
                                    <div class="flex items-center text-sm text-gray-600 mb-1">
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 text-blue-800 text-xs mr-2">{{ $p->urutan }}</span>
                                        {{ $p->dosen->user->name ?? '-' }}
                                    </div>
                                @endforeach
                            </div>

                            @php
                                $currentPenguji1 = $pendaftaran->pelaksanaanSidang->pengujiSidang->where('role', 'penguji_1')->first();
                                $currentPenguji2 = $pendaftaran->pelaksanaanSidang->pengujiSidang->where('role', 'penguji_2')->first();
                                $currentPenguji3 = $pendaftaran->pelaksanaanSidang->pengujiSidang->where('role', 'penguji_3')->first();
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="penguji_1_id" class="block text-sm font-medium text-gray-700">Penguji 1</label>
                                    <select name="penguji_1_id" id="penguji_1_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        <option value="">-- Pilih Penguji 1 --</option>
                                        @foreach($dosens as $dosen)
                                            <option value="{{ $dosen->id }}" {{ old('penguji_1_id', $currentPenguji1?->dosen_id) == $dosen->id ? 'selected' : '' }}>
                                                {{ $dosen->user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('penguji_1_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="penguji_2_id" class="block text-sm font-medium text-gray-700">Penguji 2</label>
                                    <select name="penguji_2_id" id="penguji_2_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        <option value="">-- Pilih Penguji 2 --</option>
                                        @foreach($dosens as $dosen)
                                            <option value="{{ $dosen->id }}" {{ old('penguji_2_id', $currentPenguji2?->dosen_id) == $dosen->id ? 'selected' : '' }}>
                                                {{ $dosen->user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('penguji_2_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="penguji_3_id" class="block text-sm font-medium text-gray-700">Penguji 3</label>
                                    <select name="penguji_3_id" id="penguji_3_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        <option value="">-- Pilih Penguji 3 --</option>
                                        @foreach($dosens as $dosen)
                                            <option value="{{ $dosen->id }}" {{ old('penguji_3_id', $currentPenguji3?->dosen_id) == $dosen->id ? 'selected' : '' }}>
                                                {{ $dosen->user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('penguji_3_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-3 pt-4">
                                <a href="{{ route('koordinator.pendaftaran.index', ['jenis' => $jenis]) }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                                    Batal
                                </a>
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
