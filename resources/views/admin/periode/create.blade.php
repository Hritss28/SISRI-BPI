<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('admin.periode.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Tambah Periode</h1>
            <p class="text-sm text-gray-500 mt-1">Tambah periode akademik baru</p>
        </div>

        <!-- Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <form action="{{ route('admin.periode.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Nama -->
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Periode <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Semester Ganjil 2024/2025">
                    @error('nama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tahun Akademik -->
                <div>
                    <label for="tahun_akademik" class="block text-sm font-medium text-gray-700 mb-2">
                        Tahun Akademik <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="tahun_akademik" name="tahun_akademik" value="{{ old('tahun_akademik') }}" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="2024/2025">
                    @error('tahun_akademik')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis -->
                <div>
                    <label for="jenis" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Semester <span class="text-red-500">*</span>
                    </label>
                    <select id="jenis" name="jenis" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="ganjil" {{ old('jenis') === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                        <option value="genap" {{ old('jenis') === 'genap' ? 'selected' : '' }}>Genap</option>
                    </select>
                    @error('jenis')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Tanggal Mulai -->
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('tanggal_mulai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('tanggal_selesai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Status Aktif -->
                <div class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">
                        Set sebagai periode aktif
                    </label>
                </div>
                <p class="text-sm text-gray-500 -mt-4">
                    Jika dicentang, periode lain akan otomatis dinonaktifkan.
                </p>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.periode.index') }}" 
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
