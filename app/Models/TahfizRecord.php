<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahfizRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'surah_name',
        'ayat',
        'predicate',
        'date',
        'notes',
    ];

    /**
     * Relasi ke model Student (Siswa)
     * Menggunakan foreign key student_id yang merujuk ke tabel students
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Relasi ke Guru Penyimak
     * (Asumsi guru masih menggunakan tabel users. Jika menggunakan tabel teachers, ubah ke Teacher::class)
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Daftar Surah Juz 30
     */
    public static function getJuz30Surahs()
    {
        return [
            '78. An-Naba\'', '79. An-Nazi\'at', '80. \'Abasa', '81. At-Takwir',
            '82. Al-Infitar', '83. Al-Mutaffifin', '84. Al-Insyiqaq', '85. Al-Buruj',
            '86. At-Tariq', '87. Al-A\'la', '88. Al-Gasyiyah', '89. Al-Fajr',
            '90. Al-Balad', '91. Asy-Syams', '92. Al-Lail', '93. Ad-Duha',
            '94. Al-Insyirah', '95. At-Tin', '96. Al-\'Alaq', '97. Al-Qadr',
            '98. Al-Bayyinah', '99. Az-Zalzalah', '100. Al-\'Adiyat', '101. Al-Qari\'ah',
            '102. At-Takasur', '103. Al-\'Asr', '104. Al-Humazah', '105. Al-Fil',
            '106. Quraisy', '107. Al-Ma\'un', '108. Al-Kausar', '109. Al-Kafirun',
            '110. An-Nasr', '111. Al-Lahab', '112. Al-Ikhlas', '113. Al-Falaq', '114. An-Nas'
        ];
    }
}
