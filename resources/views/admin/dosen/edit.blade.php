<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Dosen</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $dosen->user->name ?? $dosen->nama }}</p>
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
            <form action="{{ route('admin.dosen.update', $dosen) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- NIP -->
                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                        <input type="text" name="nip" id="nip" value="{{ old('nip', $dosen->nip) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('nip')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- NIDN -->
                    <div>
                        <label for="nidn" class="block text-sm font-medium text-gray-700">NIDN</label>
                        <input type="text" name="nidn" id="nidn" value="{{ old('nidn', $dosen->nidn) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('nidn')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama -->
                    <div class="md:col-span-2">
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama', $dosen->user->name ?? $dosen->nama) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('nama')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $dosen->email) }}" required
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
                                <option value="{{ $prodi->id }}" {{ old('prodi_id', $dosen->prodi_id) == $prodi->id ? 'selected' : '' }}>
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
                        <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', $dosen->no_hp) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('no_hp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="flex items-center mt-6">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $dosen->user->is_active ?? false) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Status Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <a href="{{ route('admin.dosen.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
