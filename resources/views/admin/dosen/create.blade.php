<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Tambah Dosen</h1>
                <p class="text-sm text-gray-500 mt-1">Tambah data dosen baru ke sistem</p>
            </div>
            <a href="{{ route('admin.dosen.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
        </div>

        <!-- Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <form action="{{ route('admin.dosen.store') }}" method="POST" class="p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- NIP -->
                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                        <input type="text" name="nip" id="nip" value="{{ old('nip') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('nip')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- NIDN -->
                    <div>
                        <label for="nidn" class="block text-sm font-medium text-gray-700">NIDN</label>
                        <input type="text" name="nidn" id="nidn" value="{{ old('nidn') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('nidn')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama -->
                    <div class="md:col-span-2">
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('nama')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Prodi -->
                    <div>
                        <label for="prodi_id" class="block text-sm font-medium text-gray-700">Program Studi <span class="text-red-500">*</span></label>
                        <select name="prodi_id" id="prodi_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Prodi</option>
                            @foreach($prodis as $prodi)
                                <option value="{{ $prodi->id }}" {{ old('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('prodi_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- No HP -->
                    <div>
                        <label for="no_hp" class="block text-sm font-medium text-gray-700">No. HP</label>
                        <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('no_hp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" id="password" required minlength="8"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <a href="{{ route('admin.dosen.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
