<?php

namespace App\Services;

use App\Models\PositiveWord;
use App\Models\NegativeWord;
use Illuminate\Support\Facades\Cache;

class SentimentAnalysisService
{
    /**
     * Menganalisis sentimen dari sebuah teks (judul + konten).
     * Mengembalikan array berupa sentimen dominan dan persentase skor.
     */
    public function analyze(string $text): array
    {
        if (empty(trim($text))) {
            return [
                'sentiment' => 'Neutral',
                'score' => 0,
                'positive_ratio' => 0.0,
                'negative_ratio' => 0.0,
                'neutral_ratio' => 100.0,
                'positive_count' => 0,
                'negative_count' => 0,
            ];
        }

        // Cache daftar kata agar tidak hit database terus-menerus
        $positiveWords = Cache::remember('lexicon_positive_words', 86400, function () {
            return PositiveWord::pluck('word')->map(fn($w) => strtolower(trim($w)))->toArray();
        });

        $negativeWords = Cache::remember('lexicon_negative_words', 86400, function () {
            return NegativeWord::pluck('word')->map(fn($w) => strtolower(trim($w)))->toArray();
        });

        $posLookup = array_flip($positiveWords);
        $negLookup = array_flip($negativeWords);

        // Bersihkan teks & tokenisasi kata
        $cleanText = strtolower($text);
        // Split berdasarkan spasi dan tanda baca umum
        $words = preg_split('/[\s,\.!\?\(\)"\-\:\;\'\/\\\]+/', $cleanText, -1, PREG_SPLIT_NO_EMPTY);

        $posCount = 0;
        $negCount = 0;

        foreach ($words as $word) {
            if (isset($posLookup[$word])) {
                $posCount++;
            }
            if (isset($negLookup[$word])) {
                $negCount++;
            }
        }

        $totalMatches = $posCount + $negCount;

        if ($totalMatches === 0) {
            return [
                'sentiment' => 'Neutral',
                'score' => 0,
                'positive_ratio' => 0.0,
                'negative_ratio' => 0.0,
                'neutral_ratio' => 100.0,
                'positive_count' => 0,
                'negative_count' => 0,
            ];
        }

        $posRatio = round(($posCount / $totalMatches) * 100, 1);
        $negRatio = round(($negCount / $totalMatches) * 100, 1);
        $neutralRatio = 0.0; // Jika ada kata kunci yang cocok, rasionya terbagi antara positif & negatif

        // Sentiment classification
        if ($posCount > $negCount) {
            $sentiment = 'Positive';
        } elseif ($negCount > $posCount) {
            $sentiment = 'Negative';
        } else {
            $sentiment = 'Neutral';
        }

        // Skor sentimen bersih (-100 hingga +100)
        // Score = (posCount - negCount) / totalMatches * 100
        $score = round((($posCount - $negCount) / $totalMatches) * 100);

        return [
            'sentiment' => $sentiment,
            'score' => (int) $score,
            'positive_ratio' => $posRatio,
            'negative_ratio' => $negRatio,
            'neutral_ratio' => $neutralRatio,
            'positive_count' => $posCount,
            'negative_count' => $negCount,
        ];
    }
}
