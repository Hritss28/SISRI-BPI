<x-app-layout>
    <div class="max-w-4xl mx-auto">
        <!-- Page Title -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Ajukan Topik Skripsi</h1>
            <a href="{{ route('mahasiswa.topik.index') }}" 
               class="text-gray-600 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6">
                <form action="{{ route('mahasiswa.topik.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Step Indicator -->
                    <div class="flex items-center justify-center mb-8">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">1</div>
                            <span class="ml-2 text-sm font-medium text-blue-600">Informasi Topik</span>
                        </div>
                        <div class="w-16 h-1 bg-blue-200 mx-4"></div>
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-blue-200 text-blue-600 flex items-center justify-center font-bold">2</div>
                            <span class="ml-2 text-sm font-medium text-gray-500">Pilih Pembimbing</span>
                        </div>
                    </div>

                    <!-- Section 1: Informasi Topik -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm mr-2">1</span>
                            Informasi Topik
                        </h2>

                        <!-- Bidang Minat -->
                        <div class="mb-4">
                            <label for="bidang_minat_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Bidang Minat <span class="text-red-500">*</span>
                            </label>
                            <select name="bidang_minat_id" id="bidang_minat_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('bidang_minat_id') border-red-500 @enderror">
                                <option value="">-- Pilih Bidang Minat --</option>
                                @foreach($bidangMinats as $bidang)
                                    <option value="{{ $bidang->id }}" {{ old('bidang_minat_id') == $bidang->id ? 'selected' : '' }}>
                                        {{ $bidang->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('bidang_minat_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Judul -->
                        <div class="mb-4">
                            <label for="judul" class="block text-sm font-medium text-gray-700 mb-1">
                                Judul Skripsi <span class="text-red-500">*</span>
                            </label>
                            <textarea name="judul" id="judul" rows="3" required
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('judul') border-red-500 @enderror"
                                      placeholder="Masukkan judul skripsi Anda">{{ old('judul') }}</textarea>
                            <p class="text-gray-500 text-xs mt-1">Maksimal 500 karakter</p>
                            @error('judul')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- File Proposal -->
                        <div class="mb-4">
                            <label for="file_proposal" class="block text-sm font-medium text-gray-700 mb-1">
                                File Proposal (Opsional)
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file_proposal" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                            <span>Upload file</span>
                                            <input id="file_proposal" name="file_proposal" type="file" class="sr-only" accept=".pdf">
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PDF hingga 5MB</p>
                                </div>
                            </div>
                            <p id="file-name" class="text-sm text-green-600 mt-2"></p>
                            @error('file_proposal')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Section 2: Pilih Pembimbing -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm mr-2">2</span>
                            Pilih Dosen Pembimbing
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Pembimbing 1 -->
                            <div>
                                <label for="pembimbing_1_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pembimbing 1 <span class="text-red-500">*</span>
                                </label>
                                <select name="pembimbing_1_id" id="pembimbing_1_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('pembimbing_1_id') border-red-500 @enderror">
                                    <option value="">-- Pilih Pembimbing 1 --</option>
                                    @foreach($dosens as $dosen)
                                        <option value="{{ $dosen->id }}" {{ old('pembimbing_1_id') == $dosen->id ? 'selected' : '' }}>
                                            {{ $dosen->nama }} ({{ $dosen->nidn ?? $dosen->nip ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('pembimbing_1_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Pembimbing 2 -->
                            <div>
                                <label for="pembimbing_2_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pembimbing 2 <span class="text-red-500">*</span>
                                </label>
                                <select name="pembimbing_2_id" id="pembimbing_2_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('pembimbing_2_id') border-red-500 @enderror">
                                    <option value="">-- Pilih Pembimbing 2 --</option>
                                    @foreach($dosens as $dosen)
                                        <option value="{{ $dosen->id }}" {{ old('pembimbing_2_id') == $dosen->id ? 'selected' : '' }}>
                                            {{ $dosen->nama }} ({{ $dosen->nidn ?? $dosen->nip ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('pembimbing_2_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="text-sm text-yellow-800">
                                        <strong>Perhatian:</strong> Pembimbing 1 dan Pembimbing 2 harus berbeda. Pastikan Anda sudah berkonsultasi dengan dosen yang dipilih sebelum mengajukan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t">
                        <a href="{{ route('mahasiswa.topik.index') }}" 
                           class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Ajukan Topik
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show selected file name
        document.getElementById('file_proposal').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : '';
            document.getElementById('file-name').textContent = fileName ? 'File dipilih: ' + fileName : '';
        });

        // Prevent same pembimbing selection
        document.getElementById('pembimbing_1_id').addEventListener('change', function() {
            var selected = this.value;
            var pembimbing2 = document.getElementById('pembimbing_2_id');
            
            // Reset all options
            for (var i = 0; i < pembimbing2.options.length; i++) {
                pembimbing2.options[i].disabled = false;
            }
            
            // Disable selected option in pembimbing 2
            if (selected) {
                for (var i = 0; i < pembimbing2.options.length; i++) {
                    if (pembimbing2.options[i].value === selected) {
                        pembimbing2.options[i].disabled = true;
                    }
                }
            }
        });

        document.getElementById('pembimbing_2_id').addEventListener('change', function() {
            var selected = this.value;
            var pembimbing1 = document.getElementById('pembimbing_1_id');
            
            // Reset all options
            for (var i = 0; i < pembimbing1.options.length; i++) {
                pembimbing1.options[i].disabled = false;
            }
            
            // Disable selected option in pembimbing 1
            if (selected) {
                for (var i = 0; i < pembimbing1.options.length; i++) {
                    if (pembimbing1.options[i].value === selected) {
                        pembimbing1.options[i].disabled = true;
                    }
                }
            }
        });
    </script>
</x-app-layout>
