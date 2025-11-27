<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            @php
                $typeFromUrl = request('type');
                $backUrl = $typeFromUrl ? route('admin.unit.index', ['type' => $typeFromUrl]) : route('admin.unit.index');
                $pageTitle = match($typeFromUrl) {
                    'fakultas' => 'Tambah Fakultas',
                    'prodi' => 'Tambah Program Studi',
                    default => 'Tambah Unit',
                };
            @endphp
            <a href="{{ $backUrl }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-800">{{ $pageTitle }}</h1>
            <p class="text-sm text-gray-500 mt-1">Tambah fakultas, jurusan, atau program studi</p>
        </div>

        <!-- Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <form action="{{ route('admin.unit.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Kode -->
                <div>
                    <label for="kode" class="block text-sm font-medium text-gray-700 mb-2">
                        Kode Unit <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="kode" name="kode" value="{{ old('kode') }}" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="FT, TI, S1-TI">
                    @error('kode')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama -->
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Unit <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Fakultas Teknik, Teknik Informatika">
                    @error('nama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipe -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipe Unit <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Pilih Tipe --</option>
                        <option value="fakultas" {{ old('type', $typeFromUrl) === 'fakultas' ? 'selected' : '' }}>Fakultas</option>
                        <option value="jurusan" {{ old('type', $typeFromUrl) === 'jurusan' ? 'selected' : '' }}>Jurusan</option>
                        <option value="prodi" {{ old('type', $typeFromUrl) === 'prodi' ? 'selected' : '' }}>Program Studi</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Parent -->
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Parent Unit
                    </label>
                    <select id="parent_id" name="parent_id"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Tidak Ada (Top Level) --</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                [{{ ucfirst($parent->type) }}] {{ $parent->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        Pilih parent untuk jurusan (fakultas) atau program studi (jurusan).
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                    <a href="{{ $backUrl }}" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
