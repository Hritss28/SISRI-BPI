<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Jadwal {{ $jadwal->jenis === 'seminar_proposal' ? 'Seminar Proposal' : 'Sidang Skripsi' }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('koordinator.penjadwalan.update', $jadwal) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Jenis</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($jadwal->jenis === 'seminar_proposal')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Seminar Proposal
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Sidang Skripsi
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div class="mb-4">
                            <label for="nama" class="block text-sm font-medium text-gray-700">Nama Jadwal</label>
                            <input type="text" name="nama" id="nama" value="{{ old('nama', $jadwal->nama) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Contoh: Sidang Periode I 2024/2025" required>
                            @error('nama')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="periode_id" class="block text-sm font-medium text-gray-700">Periode Akademik</label>
                            <select name="periode_id" id="periode_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">-- Pilih Periode --</option>
                                @foreach($periodes as $periode)
                                    <option value="{{ $periode->id }}" {{ old('periode_id', $jadwal->periode_id) == $periode->id ? 'selected' : '' }}>
                                        {{ $periode->tahun_akademik }} - {{ ucfirst($periode->semester) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('periode_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="tanggal_buka" class="block text-sm font-medium text-gray-700">Tanggal Buka Pendaftaran</label>
                                <input type="date" name="tanggal_buka" id="tanggal_buka" 
                                    value="{{ old('tanggal_buka', $jadwal->tanggal_buka->format('Y-m-d')) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                @error('tanggal_buka')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="tanggal_tutup" class="block text-sm font-medium text-gray-700">Tanggal Tutup Pendaftaran</label>
                                <input type="date" name="tanggal_tutup" id="tanggal_tutup" 
                                    value="{{ old('tanggal_tutup', $jadwal->tanggal_tutup->format('Y-m-d')) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                @error('tanggal_tutup')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $jadwal->is_active) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Aktifkan jadwal ini</span>
                            </label>
                        </div>

                        @php
                            $jenisParam = $jadwal->jenis === 'seminar_proposal' ? 'sempro' : 'sidang';
                        @endphp

                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('koordinator.penjadwalan.index', ['jenis' => $jenisParam]) }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                                Batal
                            </a>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>