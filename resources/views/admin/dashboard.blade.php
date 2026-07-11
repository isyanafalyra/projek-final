<x-app-layout>
    <!-- Header Section -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="text-white fw-bold mb-0">Admin Control Panel</h2>
            <p class="text-secondary mb-0">Manajemen Pengguna, Data Koordinat Pelabuhan, dan Publikasi Artikel (CMS)</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary-custom btn-sm">
                <i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Dashboard User
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <div class="mb-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show bg-success bg-opacity-20 text-success border-success border-opacity-30 rounded-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show bg-danger bg-opacity-20 text-danger border-danger border-opacity-30 rounded-3" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show bg-danger bg-opacity-20 text-danger border-danger border-opacity-30 rounded-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <strong>Kesalahan input data:</strong>
                <ul class="mb-0 mt-1 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Navigation Tab Pills -->
    <div class="d-flex justify-content-center justify-content-md-start">
        <ul class="nav nav-pills nav-pills-custom" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="users-tab" data-bs-toggle="pill" data-bs-target="#users-content" type="button" role="tab">
                    <i class="fa-solid fa-users me-2"></i>Manajemen User
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ports-tab" data-bs-toggle="pill" data-bs-target="#ports-content" type="button" role="tab">
                    <i class="fa-solid fa-anchor me-2"></i>Manajemen Pelabuhan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="articles-tab" data-bs-toggle="pill" data-bs-target="#articles-content" type="button" role="tab">
                    <i class="fa-solid fa-file-pen me-2"></i>Posting Artikel (CMS)
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content" id="adminTabsContent">

        <!-- TAB 1: User Management -->
        <div class="tab-pane fade show active" id="users-content" role="tabpanel" aria-labelledby="users-tab">
            <div class="glass-card p-4">
                <h5 class="text-white fw-bold mb-4"><i class="fa-solid fa-users-gear text-info me-2"></i>Daftar Pengguna Terdaftar</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle border-secondary">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Tanggal Bergabung</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $u)
                                <tr>
                                    <td class="text-white fw-medium">{{ $u->name }}</td>
                                    <td>{{ $u->email }}</td>
                                    <td>
                                        @if ($u->is_admin)
                                            <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 px-3 py-1.5 rounded-pill">Administrator</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-20 text-secondary border border-secondary border-opacity-30 px-3 py-1.5 rounded-pill">User Biasa</span>
                                        @endif
                                    </td>
                                    <td>{{ $u->created_at->format('d M Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <!-- Toggle Role Form -->
                                            <form action="{{ route('admin.users.toggle-role', $u->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-info" {{ Auth::id() === $u->id ? 'disabled' : '' }} title="Ubah Role">
                                                    <i class="fa-solid fa-arrows-spin me-1"></i> Toggle Role
                                                </button>
                                            </form>
                                            
                                            <!-- Delete User Form -->
                                            <form action="{{ route('admin.users.delete', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" {{ Auth::id() === $u->id ? 'disabled' : '' }} title="Hapus User">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: Port Management -->
        <div class="tab-pane fade" id="ports-content" role="tabpanel" aria-labelledby="ports-tab">
            <!-- Form Card (Add/Edit) -->
            <div class="glass-card p-4 mb-4" id="portFormCard">
                <h5 class="text-white fw-bold mb-3" id="portFormTitle"><i class="fa-solid fa-circle-plus text-info me-2"></i>Tambah Pelabuhan Baru</h5>
                
                <form id="portForm" method="POST" action="{{ route('admin.ports.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="portFormMethod" value="POST">
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label form-label-custom">NAMA PELABUHAN</label>
                            <input type="text" name="name" id="portNameInput" class="form-control form-control-custom" required placeholder="Tanjung Priok">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label form-label-custom">KODE (WPI)</label>
                            <input type="text" name="code" id="portCodeInput" class="form-control form-control-custom" placeholder="IDTPP">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label form-label-custom">NEGARA ASAL</label>
                            <select name="country_id" id="portCountryInput" class="form-select form-control-custom" required>
                                <option value="" disabled selected>Pilih Negara</option>
                                @foreach ($countries as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->iso_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label form-label-custom">LATITUDE</label>
                            <input type="number" step="any" name="latitude" id="portLatInput" class="form-control form-control-custom" required placeholder="-6.1">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label form-label-custom">LONGITUDE</label>
                            <input type="number" step="any" name="longitude" id="portLngInput" class="form-control form-control-custom" required placeholder="106.8">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" id="cancelPortBtn" class="btn btn-secondary-custom d-none" onclick="resetPortForm()">Batal</button>
                        <button type="submit" id="savePortBtn" class="btn btn-primary-custom">Simpan Pelabuhan</button>
                    </div>
                </form>
            </div>

            <!-- List Ports -->
            <div class="glass-card p-4">
                <h5 class="text-white fw-bold mb-4"><i class="fa-solid fa-list text-info me-2"></i>Daftar Koordinat Pelabuhan</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle border-secondary">
                        <thead>
                            <tr>
                                <th>Nama Pelabuhan</th>
                                <th>Kode</th>
                                <th>Negara</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ports as $p)
                                <tr>
                                    <td class="text-white fw-medium">{{ $p->name }}</td>
                                    <td><code>{{ $p->code ?? '-' }}</code></td>
                                    <td>{{ $p->country->name }}</td>
                                    <td>{{ $p->latitude }}</td>
                                    <td>{{ $p->longitude }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <!-- Edit Trigger Button -->
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="editPort({{ json_encode($p) }})" title="Edit Pelabuhan">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                            
                                            <!-- Delete Port Form -->
                                            <form action="{{ route('admin.ports.delete', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelabuhan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Pelabuhan">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: Article Management (CMS) -->
        <div class="tab-pane fade" id="articles-content" role="tabpanel" aria-labelledby="articles-tab">
            <!-- Form Card (Add/Edit) -->
            <div class="glass-card p-4 mb-4" id="articleFormCard">
                <h5 class="text-white fw-bold mb-3" id="articleFormTitle"><i class="fa-solid fa-circle-plus text-info me-2"></i>Tulis Artikel Baru</h5>
                
                <form id="articleForm" method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="articleFormMethod" value="POST">

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label form-label-custom">JUDUL ARTIKEL</label>
                            <input type="text" name="title" id="articleTitleInput" class="form-control form-control-custom" required placeholder="Analisis Dampak El Nino Terhadap Logistik Selat Malaka">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label form-label-custom">TANGGAL PUBLIKASI (KOSONGKAN = SEKARANG)</label>
                            <input type="datetime-local" name="published_at" id="articleDateInput" class="form-control form-control-custom">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-custom">UPLOAD GAMBAR COVER (MAKS 2MB)</label>
                        <input type="file" name="image" id="articleImageInput" class="form-control form-control-custom" accept="image/*">
                        <div id="currentCoverDisplay" class="mt-2 d-none">
                            <span class="text-secondary small d-block">Cover Saat Ini:</span>
                            <img id="currentCoverImg" src="" class="rounded border border-secondary mt-1" style="height: 60px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-custom">KONTEN ARTIKEL</label>
                        <textarea name="content" id="articleContentInput" rows="6" class="form-control form-control-custom" required placeholder="Tuliskan analisis atau opini riset rantai pasok global di sini..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" id="cancelArticleBtn" class="btn btn-secondary-custom d-none" onclick="resetArticleForm()">Batal</button>
                        <button type="submit" id="saveArticleBtn" class="btn btn-primary-custom">Terbitkan Artikel</button>
                    </div>
                </form>
            </div>

            <!-- List Articles -->
            <div class="glass-card p-4">
                <h5 class="text-white fw-bold mb-4"><i class="fa-solid fa-list text-info me-2"></i>Daftar Artikel Logistik</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle border-secondary">
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Judul Artikel</th>
                                <th>Penulis</th>
                                <th>Tanggal Terbit</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($articles as $art)
                                <tr>
                                    <td>
                                        @if ($art->image_path)
                                            <img src="{{ asset($art->image_path) }}" class="rounded" style="width: 50px; height: 35px; object-fit: cover;">
                                        @else
                                            <span class="text-secondary small italic">No Cover</span>
                                        @endif
                                    </td>
                                    <td class="text-white fw-medium">{{ $art->title }}</td>
                                    <td>{{ $art->user->name }}</td>
                                    <td>{{ $art->published_at ? \Carbon\Carbon::parse($art->published_at)->format('d M Y H:i') : '-' }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <!-- Edit Trigger Button -->
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="editArticle({{ json_encode($art) }})" title="Edit Artikel">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                            
                                            <!-- Delete Article Form -->
                                            <form action="{{ route('admin.articles.delete', $art->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Artikel">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            // --- CRUD PELABUHAN JS HELPERS ---
            function editPort(port) {
                // Change Form target to UPDATE
                document.getElementById('portForm').action = `/admin/ports/${port.id}`;
                document.getElementById('portFormMethod').value = 'PUT';
                document.getElementById('portFormTitle').innerHTML = `<i class="fa-solid fa-pen-to-square text-info me-2"></i>Edit Pelabuhan: ${port.name}`;
                document.getElementById('savePortBtn').textContent = 'Perbarui Pelabuhan';
                
                // Populate Inputs
                document.getElementById('portNameInput').value = port.name;
                document.getElementById('portCodeInput').value = port.code || '';
                document.getElementById('portCountryInput').value = port.country_id;
                document.getElementById('portLatInput').value = port.latitude;
                document.getElementById('portLngInput').value = port.longitude;

                // Show Cancel Button
                document.getElementById('cancelPortBtn').classList.remove('d-none');
                
                // Scroll up to form card
                document.getElementById('portFormCard').scrollIntoView({ behavior: 'smooth' });
            }

            function resetPortForm() {
                // Reset Form Action to STORE
                document.getElementById('portForm').action = "{{ route('admin.ports.store') }}";
                document.getElementById('portFormMethod').value = 'POST';
                document.getElementById('portFormTitle').innerHTML = `<i class="fa-solid fa-circle-plus text-info me-2"></i>Tambah Pelabuhan Baru`;
                document.getElementById('savePortBtn').textContent = 'Simpan Pelabuhan';
                
                // Reset Fields
                document.getElementById('portForm').reset();
                
                // Hide Cancel Button
                document.getElementById('cancelPortBtn').classList.add('d-none');
            }

            // --- CRUD ARTIKEL / CMS JS HELPERS ---
            function editArticle(article) {
                // Change Form target to UPDATE
                document.getElementById('articleForm').action = `/admin/articles/${article.id}`;
                document.getElementById('articleFormMethod').value = 'PUT';
                document.getElementById('articleFormTitle').innerHTML = `<i class="fa-solid fa-pen-to-square text-info me-2"></i>Edit Artikel: ${article.title}`;
                document.getElementById('saveArticleBtn').textContent = 'Perbarui Artikel';

                // Populate Inputs
                document.getElementById('articleTitleInput').value = article.title;
                document.getElementById('articleContentInput').value = article.content;
                
                if (article.published_at) {
                    // Convert to datetime-local compatible string (YYYY-MM-DDTHH:MM)
                    const dateObj = new Date(article.published_at);
                    const localISO = new Date(dateObj.getTime() - dateObj.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
                    document.getElementById('articleDateInput').value = localISO;
                } else {
                    document.getElementById('articleDateInput').value = '';
                }

                // Handle Cover display
                const coverContainer = document.getElementById('currentCoverDisplay');
                const coverImg = document.getElementById('currentCoverImg');
                if (article.image_path) {
                    coverImg.src = `/${article.image_path}`;
                    coverContainer.classList.remove('d-none');
                } else {
                    coverContainer.classList.add('d-none');
                }

                // Show Cancel Button
                document.getElementById('cancelArticleBtn').classList.remove('d-none');
                
                // Scroll up to form card
                document.getElementById('articleFormCard').scrollIntoView({ behavior: 'smooth' });
            }

            function resetArticleForm() {
                // Reset Form Action to STORE
                document.getElementById('articleForm').action = "{{ route('admin.articles.store') }}";
                document.getElementById('articleFormMethod').value = 'POST';
                document.getElementById('articleFormTitle').innerHTML = `<i class="fa-solid fa-circle-plus text-info me-2"></i>Tulis Artikel Baru`;
                document.getElementById('saveArticleBtn').textContent = 'Terbitkan Artikel';

                // Reset Fields
                document.getElementById('articleForm').reset();
                document.getElementById('currentCoverDisplay').classList.add('d-none');

                // Hide Cancel Button
                document.getElementById('cancelArticleBtn').classList.add('d-none');
            }
        </script>
    @endpush
</x-app-layout>
