<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Inventaris Barang & Stok') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('inventory.history') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow text-sm transition duration-150 ease-in-out">
                    Riwayat Transaksi
                </a>
                <button onclick="openModal('importModal')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow text-sm transition duration-150 ease-in-out">
                    <i class="fas fa-file-excel mr-1"></i> Import
                </button>
                <button onclick="openModal('addItemModal')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow text-sm transition duration-150 ease-in-out">
                    + Tambah Barang Baru
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Alerts --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 shadow-sm">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 shadow-sm">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Tabel Master Barang --}}
            <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa Stok</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi (In/Out)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($items as $item)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->code }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="font-semibold">{{ $item->name }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $item->description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full {{ $item->stock > 5 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $item->stock }} {{ $item->unit }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium flex justify-center space-x-2">
                                        <button onclick="openTransactionModal('{{ $item->id }}', '{{ $item->name }}', '{{ $item->unit }}', 'in')" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs shadow-sm transition">
                                            + Masuk
                                        </button>
                                        <button onclick="openTransactionModal('{{ $item->id }}', '{{ $item->name }}', '{{ $item->unit }}', 'out')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs shadow-sm transition {{ $item->stock <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $item->stock <= 0 ? 'disabled' : '' }}>
                                            - Keluar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada data barang.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-4">
                {{ $items->links() }}
            </div>

        </div>
    </div>

    {{-- DataList --}}
    <datalist id="unitList">
        <option value="Pcs"><option value="Rim"><option value="Box"><option value="Lusin">
        <option value="Pak"><option value="Kg"><option value="Liter"><option value="Unit">
    </datalist>
    <datalist id="fundingSources">
        <option value="Dana BOS"><option value="APBD / Pemerintah"><option value="Dana Yayasan">
        <option value="Sumbangan / Hibah"><option value="Dana Mandiri">
    </datalist>

    {{-- Modal Import Data --}}
    <div id="importModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900 bg-opacity-50 transition-opacity" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4 border-b pb-2"><i class="fas fa-file-excel text-green-600 me-2"></i> Import Data Inventaris</h3>
                    
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-4 text-sm text-blue-800">
                        <p class="font-bold mb-1"><i class="fas fa-info-circle"></i> Petunjuk Import:</p>
                        <ol class="list-decimal pl-4 space-y-1">
                            <li>Gunakan format Excel (.xlsx, .xls, .csv).</li>
                            <li>Pastikan nama kolom / <em>header</em> pada baris pertama persis seperti template.</li>
                            <li><a href="{{ route('inventory.template') }}" class="font-bold text-blue-600 hover:text-blue-800 underline">Unduh Template CSV Disini</a></li>
                        </ol>
                    </div>

                    <form action="{{ route('inventory.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel / CSV</label>
                            <input type="file" name="file" required accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-md p-2">
                        </div>
                        
                        <div class="flex justify-end space-x-2 mt-5 pt-4 border-t border-gray-100">
                            <button type="button" onclick="closeModal('importModal')" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded shadow-sm text-sm font-medium">Batal</button>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium">Mulai Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Master Barang --}}
    <div id="addItemModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900 bg-opacity-50 transition-opacity" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4 border-b pb-2" id="modal-title">Tambah Master Barang Baru</h3>
                    <form action="{{ route('inventory.item.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kode Barang</label>
                                <input type="text" name="code" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md border p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Satuan (Unit)</label>
                                <input type="text" name="unit" list="unitList" placeholder="Pcs, Rim, dll" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md border p-2">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                            <input type="text" name="name" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md border p-2">
                        </div>
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Stok Awal</label>
                                <input type="number" name="initial_stock" value="0" min="0" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md border p-2">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Sumber Dana <span class="text-xs text-gray-400 font-normal">(Opsional)</span></label>
                                <input type="text" name="funding_source" list="fundingSources" placeholder="Pilih / Ketik manual" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md border p-2">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Tahun Anggaran/Perolehan</label>
                            <input type="number" name="year" value="{{ date('Y') }}" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md border p-2">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Keterangan Barang</label>
                            <textarea name="description" rows="2" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md border p-2"></textarea>
                        </div>
                        <div class="flex justify-end space-x-2 mt-5 pt-4 border-t border-gray-100">
                            <button type="button" onclick="closeModal('addItemModal')" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded shadow-sm text-sm font-medium">Batal</button>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium">Simpan Barang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Transaksi Masuk/Keluar --}}
    <div id="transactionModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900 bg-opacity-50 transition-opacity" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4 border-b pb-2" id="transModalTitle">Transaksi Barang</h3>
                    <form action="{{ route('inventory.transaction.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="item_id" id="transItemId">
                        <input type="hidden" name="type" id="transType">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Barang yang Dipilih</label>
                            <input type="text" id="transItemName" disabled class="mt-1 block w-full bg-gray-100 shadow-sm sm:text-sm border-gray-300 rounded-md border p-2 font-bold text-gray-800 cursor-not-allowed">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jumlah (<span id="transItemUnit" class="text-blue-600">Qty</span>)</label>
                                <input type="number" name="quantity" min="1" value="1" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md border p-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal Transaksi</label>
                                <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md border p-2">
                            </div>
                        </div>
                        
                        {{-- Container IN (Masuk): Sumber Dana & Tahun --}}
                        <div id="inContainer" style="display: none;" class="p-4 bg-green-50 rounded-lg border border-green-100 mb-4">
                            <p class="text-xs font-semibold text-green-800 mb-2 uppercase tracking-wide">Detail Barang Masuk</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tahun Anggaran</label>
                                    <input type="number" name="year" id="transYear" value="{{ date('Y') }}" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-green-500 focus:border-green-500 rounded-md border p-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sumber Dana</label>
                                    <input type="text" name="funding_source" id="transFundingSource" list="fundingSources" placeholder="Pilih / Ketik" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-green-500 focus:border-green-500 rounded-md border p-2">
                                </div>
                            </div>
                        </div>

                        {{-- Container OUT (Keluar): Pengambil --}}
                        <div id="outContainer" style="display: none;" class="p-4 bg-red-50 rounded-lg border border-red-100 mb-4">
                            <p class="text-xs font-semibold text-red-800 mb-2 uppercase tracking-wide">Detail Barang Keluar</p>
                            <label class="block text-sm font-medium text-gray-700">Nama Penerima / Pengambil</label>
                            <input type="text" name="receiver" id="transReceiver" placeholder="Cth: Pak Budi / Ruang Guru" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-red-500 focus:border-red-500 rounded-md border p-2">
                            <p class="text-xs text-gray-500 mt-1">Siapa yang meminta/mengambil barang ini?</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Keterangan Tambahan</label>
                            <textarea name="notes" rows="2" placeholder="Tuliskan catatan opsional di sini..." class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md border p-2"></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-2 mt-5 pt-4 border-t border-gray-100">
                            <button type="button" onclick="closeModal('transactionModal')" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded shadow-sm text-sm font-medium">Batal</button>
                            <button type="submit" id="transSubmitBtn" class="bg-blue-600 text-white px-4 py-2 rounded shadow-sm text-sm font-medium hover:opacity-90 transition">Simpan Transaksi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mencegah resubmission saat refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        function openModal(id) { 
            document.getElementById(id).classList.remove('hidden'); 
        }
        function closeModal(id) { 
            document.getElementById(id).classList.add('hidden'); 
        }

        function openTransactionModal(itemId, itemName, itemUnit, type) {
            document.getElementById('transItemId').value = itemId;
            document.getElementById('transItemName').value = itemName + ' (' + itemUnit + ')';
            document.getElementById('transItemUnit').innerText = itemUnit;
            document.getElementById('transType').value = type;
            
            const title = document.getElementById('transModalTitle');
            const btn = document.getElementById('transSubmitBtn');
            
            const inContainer = document.getElementById('inContainer');
            const outContainer = document.getElementById('outContainer');
            
            const inputYear = document.getElementById('transYear');
            const inputFunding = document.getElementById('transFundingSource');
            const inputReceiver = document.getElementById('transReceiver');
            
            if(type === 'in') {
                title.innerText = 'Catat Barang Masuk (+)';
                btn.innerText = 'Simpan Barang Masuk';
                btn.className = 'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition';
                
                inContainer.style.display = 'block';
                outContainer.style.display = 'none';
                
                inputFunding.required = true;
                inputYear.required = true;
                inputReceiver.required = false;
                inputReceiver.value = '';
            } else {
                title.innerText = 'Catat Barang Keluar (-)';
                btn.innerText = 'Simpan Barang Keluar';
                btn.className = 'bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition';
                
                inContainer.style.display = 'none';
                outContainer.style.display = 'block';
                
                inputReceiver.required = true;
                inputFunding.required = false;
                inputYear.required = false;
                inputFunding.value = '';
            }
            
            openModal('transactionModal');
        }
    </script>
</x-app-layout>