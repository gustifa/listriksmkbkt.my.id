<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin Orang Tua - {{ $student->name }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Tinos:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Tinos', serif; } /* Mirip Times New Roman */
        
        /* Print Styling */
        @media print {
            @page { margin: 0; size: A4 portrait; }
            body { background: white; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            #print-area { 
                box-shadow: none; 
                margin: 0; 
                width: 100%; 
                height: 100%;
                overflow: visible;
            }
            /* Sembunyikan scrollbar saat print */
            main { overflow: visible !important; }
        }

        .paper-shadow {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 h-screen flex flex-col overflow-hidden">

    <!-- Header / Toolbar (No Print) -->
    <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shadow-sm z-20 no-print">
        <div class="flex items-center gap-3">
            <a href="{{ route('student.internships.index') }}" class="text-slate-500 hover:text-emerald-600 transition">
                <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="font-bold text-gray-800 text-lg leading-tight">Cetak Surat Izin</h1>
                <p class="text-[10px] text-slate-500">Isi data orang tua lalu cetak dokumen.</p>
            </div>
        </div>
        <div>
            <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg font-bold text-sm shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 overflow-hidden flex flex-col lg:flex-row">
        
        <!-- Panel Kiri: Form Input (No Print) -->
        <aside class="w-full lg:w-1/3 bg-white border-r border-gray-200 overflow-y-auto p-6 no-print z-10">
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 flex gap-3">
                <i class="fa-solid fa-circle-info text-blue-600 mt-1"></i>
                <div>
                    <h3 class="font-bold text-blue-800 text-sm mb-1">Instruksi</h3>
                    <ul class="text-xs text-blue-700 list-disc list-inside space-y-1">
                        <li>Isi data orang tua dengan benar.</li>
                        <li>Cetak surat ini (Gunakan kertas A4).</li>
                        <li>Tempel materai 10.000 pada kolom tanda tangan.</li>
                        <li>Minta tanda tangan orang tua.</li>
                        <li>Foto/Scan dan upload di menu sebelumnya.</li>
                    </ul>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Data Siswa (Readonly) -->
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 border-b pb-2">Data Siswa</h4>
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Nama Lengkap</label>
                            <div class="font-bold text-gray-800">{{ $student->name }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">NIS</label>
                                <div class="font-mono text-sm">{{ $student->nis }}</div>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Kelas</label>
                                <div class="text-sm">{{ $student->classroom->name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Input Data Orang Tua -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Orang Tua / Wali <span class="text-red-500">*</span></label>
                    <input type="text" id="inputNamaOrtu" class="w-full bg-white border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition" placeholder="Contoh: Budi Santoso">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Alamat Orang Tua <span class="text-red-500">*</span></label>
                    <textarea id="inputAlamat" rows="2" class="w-full bg-white border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition" placeholder="Alamat lengkap sesuai KTP..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">No. HP / WA <span class="text-red-500">*</span></label>
                    <input type="text" id="inputHp" class="w-full bg-white border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition" placeholder="08xxxxxxxxxx">
                </div>
            </div>
        </aside>

        <!-- Panel Kanan: Preview Surat -->
        <div class="flex-1 bg-gray-100 p-8 overflow-y-auto flex justify-center scroll-smooth">
            
            <!-- Kertas A4 (210mm x 297mm) -->
            <!-- Padding disesuaikan agar mirip Margin Word (Top 2cm, Right 2cm, Bottom 2cm, Left 2.5cm) -->
            <div id="print-area" class="bg-white w-[210mm] min-h-[297mm] py-[20mm] px-[25mm] paper-shadow text-black relative mx-auto">
                
                <!-- Kop Surat -->
                <table class="w-full border-b-4 border-double border-black mb-6 pb-2">
                    <tr>
                        <td width="15%" class="align-middle text-center pb-2">
                            @if(!empty($school['logo_left']))
                                <img src="{{ asset('storage/'.$school['logo_left']) }}" class="w-20 h-auto mx-auto">
                            @else
                                <div class="w-16 h-16 bg-gray-200 rounded-full mx-auto"></div>
                            @endif
                        </td>
                        <td width="70%" class="text-center align-middle pb-2">
                            <h3 class="text-[12pt] font-serif font-bold uppercase m-0 leading-tight">Pemerintah Provinsi {{ $school['provinsi_name'] ?? 'Sumatera Barat' }}</h3>
                            <h3 class="text-[12pt] font-serif font-bold uppercase m-0 leading-tight">Dinas Pendidikan</h3>
                            <h2 class="text-[16pt] font-serif font-extrabold uppercase m-0 tracking-wide mt-1 leading-tight">{{ $school['name'] }}</h2>
                            <p class="text-[9pt] font-serif m-0 mt-1 leading-tight">{{ $school['address'] }}</p>
                            <p class="text-[9pt] font-serif m-0 leading-tight">Email: {{ $school['email'] }} | Telp: {{ $school['phone'] }}</p>
                        </td>
                        <td width="15%" class="align-middle text-center pb-2">
                            @if(!empty($school['logo_right']))
                                <img src="{{ asset('storage/'.$school['logo_right']) }}" class="w-20 h-auto mx-auto">
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- Judul -->
                <div class="text-center mb-8">
                    <h1 class="text-[14pt] font-bold underline uppercase font-serif mb-1">Surat Pernyataan Izin Orang Tua</h1>
                    <p class="text-[11pt] font-serif">Nomor: _____/PKL/SMKN1-BKT/{{ date('Y') }}</p>
                </div>

                <!-- Isi Surat -->
                <div class="text-justify text-[12pt] leading-relaxed font-serif">
                    <p class="mb-4">Saya yang bertanda tangan di bawah ini:</p>

                    <table class="w-full ml-4 mb-4">
                        <tr>
                            <td class="w-48 py-1 align-top">Nama Orang Tua / Wali</td>
                            <td class="w-4 align-top">:</td>
                            <td class="font-bold align-top" id="prevNamaOrtu">......................................................</td>
                        </tr>
                        <tr>
                            <td class="py-1 align-top">Alamat</td>
                            <td class="align-top">:</td>
                            <td class="align-top" id="prevAlamat">......................................................</td>
                        </tr>
                        <tr>
                            <td class="py-1 align-top">No. HP / WA</td>
                            <td class="align-top">:</td>
                            <td class="align-top" id="prevHp">......................................................</td>
                        </tr>
                        <tr><td class="py-2" colspan="3"></td></tr>
                        <tr><td class="py-1 font-bold" colspan="3">Orang tua / Wali dari siswa:</td></tr>
                        <tr>
                            <td class="py-1 pl-4 align-top">Nama Siswa</td>
                            <td class="align-top">:</td>
                            <td class="font-bold uppercase align-top">{{ $student->name }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 pl-4 align-top">NIS / NISN</td>
                            <td class="align-top">:</td>
                            <td class="align-top font-mono">{{ $student->nis }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 pl-4 align-top">Kelas</td>
                            <td class="align-top">:</td>
                            <td class="align-top">{{ $student->classroom->name ?? '-' }}</td>
                        </tr>
                    </table>

                    <p class="mb-4 indent-8">
                        Dengan ini menyatakan <strong>menyetujui / mengizinkan</strong> anak saya tersebut di atas untuk mengikuti dan melaksanakan Praktik Kerja Lapangan (PKL) selama 6 (enam) bulan, bertempat di:
                    </p>

                    <!-- Kotak DUDI -->
                    <div class="border-2 border-black p-4 text-center my-6 font-bold text-[14pt] uppercase bg-gray-50/50">
                        {{ $myInternship->industry->name ?? '(TEMPAT PKL BELUM DIPILIH)' }}
                    </div>

                    <p class="mb-4 indent-8">
                        Saya bersedia mematuhi segala peraturan dan tata tertib yang berlaku di Sekolah maupun di tempat PKL (Dunia Kerja), serta mendukung sepenuhnya kegiatan tersebut demi kelancaran pendidikan anak saya.
                    </p>

                    <p class="indent-8">
                        Demikian surat pernyataan ini saya buat dengan sesungguhnya tanpa ada paksaan dari pihak manapun untuk dapat dipergunakan sebagaimana mestinya.
                    </p>
                </div>

                <!-- Tanda Tangan -->
                <div class="mt-16 flex justify-between text-[11pt] font-serif break-inside-avoid">
                    <div class="text-center w-[40%]">
                        <p class="mb-16">Mengetahui,<br>Kepala Bengkel / Ka. Prog</p>
                        
                        <p class="font-bold underline uppercase">{{ $school['kabeng_name'] }}</p>
                        <p>NIP. {{ $school['kabeng_nip'] }}</p>
                    </div>
                    <div class="text-center w-[40%]">
                        <p class="mb-2">Bukittinggi, <span id="currentDate">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span></p>
                        <p class="mb-4">Orang Tua / Wali Murid</p>
                        
                        <!-- Kotak Materai -->
                        <div class="w-24 h-16 border border-dashed border-gray-400 mx-auto flex items-center justify-center text-[8pt] text-gray-400 italic mb-4">
                            Materai<br>10.000
                        </div>

                        <p class="font-bold underline" id="prevSignName">(.......................................)</p>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <script>
        // Live Data Binding (Input Kiri -> Preview Kanan)
        const bindings = [
            { input: 'inputNamaOrtu', output: 'prevNamaOrtu' },
            { input: 'inputAlamat', output: 'prevAlamat' },
            { input: 'inputHp', output: 'prevHp' },
            { input: 'inputNamaOrtu', output: 'prevSignName' }, // Nama ortu juga muncul di TTD
        ];

        bindings.forEach(bind => {
            const inputEl = document.getElementById(bind.input);
            const outputEl = document.getElementById(bind.output);
            
            if(inputEl && outputEl) {
                inputEl.addEventListener('input', () => {
                    const val = inputEl.value;
                    
                    if (bind.input === 'inputNamaOrtu' && bind.output === 'prevSignName') {
                        // Format Tanda Tangan: ( Nama Jelas )
                        outputEl.innerText = val ? `( ${val} )` : '(.......................................)';
                    } else {
                        // Default: Titik-titik jika kosong
                        outputEl.innerText = val ? val : '......................................................';
                    }
                });
            }
        });
    </script>
</body>
</html>