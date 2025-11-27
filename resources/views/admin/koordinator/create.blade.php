<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('admin.koordinator.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Tambah Koordinator Prodi</h1>
            <p class="text-sm text-gray-500 mt-1">Tetapkan dosen sebagai koordinator program studi</p>
        </div>

        <!-- Flash Message -->
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <form action="{{ route('admin.koordinator.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Program Studi -->
                <div>
                    <label for="prodi_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Program Studi <span class="text-red-500">*</span>
                    </label>
                    <select id="prodi_id" name="prodi_id" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach($prodis as $prodi)
                            <option value="{{ $prodi->id }}" {{ old('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                {{ $prodi->nama }} ({{ $prodi->kode }})
                            </option>
                        @endforeach
                    </select>
                    @error('prodi_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dosen -->
                <div>
                    <label for="dosen_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Dosen <span class="text-red-500">*</span>
                    </label>
                    <select id="dosen_id" name="dosen_id" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Pilih Dosen --</option>
                        @foreach($dosens as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('dosen_id') == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->user->name }} ({{ $dosen->nip }})
                            </option>
                        @endforeach
                    </select>
                    @error('dosen_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        Dosen yang dipilih akan dijadikan koordinator untuk program studi yang dipilih.
                    </p>
                </div>

                <!-- Tahun Mulai -->
                <div>
                    <label for="tahun_mulai" class="block text-sm font-medium text-gray-700 mb-2">
                        Tahun Mulai <span class="text-red-500">*</span>
                    </label>
                    <select id="tahun_mulai" name="tahun_mulai" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Pilih Tahun --</option>
                        @for($year = date('Y') + 1; $year >= 2015; $year--)
                            <option value="{{ $year }}" {{ old('tahun_mulai', date('Y')) == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                    @error('tahun_mulai')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info -->
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Perhatian</h3>
                            <ul class="mt-1 text-sm text-yellow-700 list-disc list-inside">
                                <li>Jika prodi sudah memiliki koordinator aktif, koordinator lama akan dinonaktifkan.</li>
                                <li>Satu dosen hanya bisa menjadi koordinator di satu prodi.</li>
                                <li>Role user akan otomatis diubah menjadi "koordinator".</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.koordinator.index') }}" 
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
