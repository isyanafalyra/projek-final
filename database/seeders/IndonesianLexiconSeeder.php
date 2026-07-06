<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PositiveWord;
use App\Models\NegativeWord;

class IndonesianLexiconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positiveWords = [
            'aman', 'lancar', 'tumbuh', 'naik', 'untung', 'sukses', 'stabil', 'meningkat', 
            'baik', 'surplus', 'damai', 'pulih', 'efisien', 'optimal', 'kooperatif', 
            'ekspansi', 'sehat', 'maju', 'positif', 'sepakat', 'laba', 'berhasil', 
            'amanah', 'mudah', 'terbuka', 'aktif', 'membaik', 'kondusif', 'potensial', 
            'menjamin', 'mendukung', 'kesepakatan', 'peningkatan', 'pertumbuhan'
        ];

        $negativeWords = [
            'krisis', 'perang', 'macet', 'rusak', 'rugi', 'inflasi', 'turun', 'blokade', 
            'konflik', 'gagal', 'sanksi', 'terhambat', 'buruk', 'defisit', 'demonstrasi', 
            'bencana', 'badai', 'gempa', 'resesi', 'mogok', 'sabotase', 'tunda', 
            'memburuk', 'negatif', 'bahaya', 'jatuh', 'rugi', 'lambat', 'ancaman', 
            'terganggu', 'langka', 'mahal', 'korupsi', 'ditolak', 'pemogokan', 'macet'
        ];

        foreach ($positiveWords as $word) {
            PositiveWord::firstOrCreate(['word' => $word]);
        }

        foreach ($negativeWords as $word) {
            NegativeWord::firstOrCreate(['word' => $word]);
        }
    }
}
