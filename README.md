# Global Supply Chain Risk Intelligence Platform

Global Supply Chain Risk Intelligence Platform adalah platform berbasis web untuk memantau, menganalisis, dan memprediksi tingkat risiko rantai pasok global pada berbagai negara secara real-time. Platform ini mengintegrasikan berbagai API global pihak ketiga dan menerapkan algoritma analisis sentimen berita berbasis leksikon (*Lexicon-Based Sentiment Analysis*) serta kalkulasi bobot risiko (*Weighted Risk Model*).

---

## Fitur Utama

1.  **Global Country Dashboard**: Menampilkan informasi makroekonomi negara terpilih beserta ringkasan cuaca dan perkiraan risiko terbaru.
2.  **Risk Scoring Engine**: Visualisasi interaktif (Gauge Chart & Histori Tren) dari kalkulasi bobot risiko multi-dimensi (Cuaca, Inflasi, Sentimen Berita, Volatilitas Valuta).
3.  **Global Port Weather Overlay**: Peta interaktif dunia berbasis **Leaflet.js** yang menampilkan titik pelabuhan kontainer kargo dan integrasi popup informasi cuaca real-time langsung melalui API satelit cuaca.
4.  **Currency Impact Dashboard**: Informasi nilai tukar real-time dan visualisasi tren pergerakan volatilitas mata uang dalam 7 hari terakhir.
5.  **News Intelligence (Sentiment Analysis)**: Pengambilan berita logistik via GNews API yang dianalisis secara internal dengan kamus leksikon PHP guna menentukan sentimen berita (Positif/Negatif/Netral) disertai persentase rasio.
6.  **Country Comparison Engine**: Membandingkan profil risiko dan data makroekonomi antara dua negara secara berdampingan.
7.  **Favorite Watchlist**: Menyimpan negara-negara penting ke dalam daftar pantau user.
8.  **Admin Control Panel (CMS)**: Halaman manajemen user (toggle status admin), pengelolaan data koordinat pelabuhan, dan CMS untuk publikasi artikel/analisis riset logistik.

---

## Skema Database (18 Tabel)

Sistem ini memiliki total **18 tabel** di database untuk mendukung semua fitur analisis, audit, dan skalabilitas data:
1.  `users` - Data pengguna terdaftar (menyimpan flag `is_admin`).
2.  `password_reset_tokens` - Token reset kata sandi (default Laravel).
3.  `sessions` - Penyimpanan sesi otentikasi aktif (default Laravel).
4.  `cache` - Penyimpanan cache sistem (default Laravel).
5.  `cache_locks` - Manajemen lock cache (default Laravel).
6.  `jobs` - Antrean job sistem (default Laravel).
7.  `job_batches` - Batching job sistem (default Laravel).
8.  `failed_jobs` - Pencatatan job antrean yang gagal (default Laravel).
9.  `countries` - Informasi master negara (nama, ISO code, region).
10. `ports` - Lokasi pelabuhan kargo kontainer global beserta koordinat geografis.
11. `risk_scores` - Log riwayat kalkulasi skor risiko tiap negara.
12. `watchlists` - Daftar pantau (bookmark) negara favorit pilihan pengguna.
13. `news_caches` - Caching artikel berita dari GNews API guna efisiensi kuota.
14. `articles` - Artikel riset dan analisis logistik yang diposting oleh admin.
15. `positive_words` - Kamus leksikon kata berunsur positif untuk analisis sentimen berita.
16. `negative_words` - Kamus leksikon kata berunsur negatif untuk analisis sentimen berita.
17. `activity_logs` - Audit trail untuk melacak aktivitas CRUD yang dilakukan oleh administrator (tambah/edit/hapus port, artikel, role).
18. `currency_histories` - Penyimpanan historis pergerakan nilai tukar mata uang harian.

---

## Arsitektur & Logika Data Science

### A. Weighted Risk Model (Kalkulasi Skor Risiko)
Sistem memprediksi skor risiko negara (skala 0 - 100) menggunakan bobot berikut:
*   **Weather Risk** (Cuaca buruk pelabuhan): **Bobot 30%**
*   **Inflation Risk** (Laju inflasi negara): **Bobot 20%**
*   **Political/News Risk** (Sentimen berita logistik): **Bobot 40%**
*   **Currency Risk** (Volatilitas kurs): **Bobot 10%**

Kategori Skor Akhir:
*   `< 30` : **Low Risk** (Risiko Rendah - Hijau)
*   `30 - 60` : **Medium Risk** (Risiko Sedang - Kuning)
*   `> 60` : **High Risk** (Risiko Tinggi - Merah)

### B. Lexicon-Based Sentiment Analysis
Logika pemrosesan teks berita di backend untuk mendeteksi sentimen positif/negatif dengan membandingkan kata dalam teks berita terhadap basis data kata positif (`positive_words`) dan kata negatif (`negative_words`).

---

## Spesifikasi API Internal

Seluruh data disajikan secara dinamis melalui REST API internal berikut:
*   `GET /api/countries` - Mendapatkan daftar semua negara & data makroekonomi.
*   `GET /api/risk?country_id={id}` - Mengembalikan skor risiko terbaru dan data historis makro.
*   `GET /api/ports` - Mendapatkan daftar pelabuhan beserta koordinat latitude/longitude untuk peta Leaflet.
*   `GET /api/news?country_id={id}` - Mengambil berita terbaru yang telah dianalisis sentimennya.
*   `GET /api/currency?base={code}` - Mengembalikan konversi nilai tukar & histori tren 7 hari terakhir.
*   `POST /api/watchlist/toggle` - Menambah atau menghapus negara dari daftar pantau user (butuh login).

---

## Kredensial Pengujian Default

Anda dapat menggunakan akun pengujian berikut yang telah disediakan oleh database seeder:

*   **Akun Administrator:**
    *   Email: `admin@globalchain.com`
    *   Password: `password`
*   **Akun User Biasa:**
    *   Email: `user@globalchain.com`
    *   Password: `password`

---

## Petunjuk Instalasi & Setup

Ikuti langkah-langkah berikut untuk menjalankan proyek di lingkungan lokal Anda:

1.  **Clone Repositori & Masuk ke Direktori Proyek**
    ```bash
    cd "projek akhir global chain"
    ```

2.  **Instal Dependensi PHP (Composer)**
    ```bash
    composer install
    ```

3.  **Salin File Konfigurasi Environment**
    ```bash
    cp .env.example .env
    ```

4.  **Konfigurasi Database & API Key di `.env`**
    Sesuaikan koneksi database MySQL Anda di `.env`. Tambahkan juga API Key GNews di bagian bawah berkas:
    ```env
    DB_DATABASE=global_chain_db
    DB_USERNAME=root
    DB_PASSWORD=

    GNEWS_API_KEY=your_gnews_api_key_here
    ```

5.  **Generate Application Key**
    ```bash
    php artisan key:generate
    ```

6.  **Jalankan Migrasi & Database Seeder**
    ```bash
    php artisan migrate:fresh --seed
    ```

7.  **Jalankan Server Lokal**
    ```bash
    php artisan serve --port=8000
    ```
    Aplikasi dapat diakses melalui browser di alamat **http://127.0.0.1:8000**.
