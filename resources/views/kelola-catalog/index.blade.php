@extends('layouts.app')

@section('title', 'Kelola Catalog')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            <i class="fas fa-book me-2"></i>Kelola Catalog - {{ $company->nama ?? 'Perusahaan' }}
                        </h4>
                        <small class="text-muted">Atur dan kelola catalog perusahaan Anda</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('catalog') }}" target="_blank" class="btn btn-success btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i>Preview Catalog
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- CATALOG BUILDER -->
                    <div class="catalog-builder-container">
                        <div class="text-center mb-4">
                            <h5>Catalog Builder</h5>
                            <p class="text-muted">Buat catalog perusahaan Anda dengan mudah</p>
                        </div>
                        
                        <!-- COVER SECTION EDITOR -->
                        <div class="section-editor mb-4" id="coverSection">
                            <div class="section-header">
                                <h6><i class="fas fa-image me-2"></i>Cover Section</h6>
                                <small class="text-muted">Foto perusahaan, nama, dan deskripsi</small>
                            </div>
                            <div class="section-content">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Nama Perusahaan</label>
                                            <input type="text" class="form-control" id="companyName" value="{{ $catalogSections['cover']['company_name'] ?? $company->nama ?? '' }}" placeholder="Nama Perusahaan">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Tagline</label>
                                            <input type="text" class="form-control" id="companyTagline" value="{{ $catalogSections['cover']['company_tagline'] ?? 'BRANDING PRODUCT.' }}" placeholder="BRANDING PRODUCT.">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Deskripsi Perusahaan</label>
                                            <textarea class="form-control resizable-textarea" id="companyDescription" rows="4" placeholder="Masukkan deskripsi perusahaan Anda...">{{ $catalogSections['cover']['company_description'] ?? $company->catalog_description ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' }}</textarea>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Text Tombol</label>
                                            <input type="text" class="form-control" id="exploreText" value="{{ $catalogSections['cover']['explore_text'] ?? 'Explore' }}" placeholder="Explore">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Foto Cover</label>
                                            <input type="file" id="coverPhotoInput" class="form-control" accept="image/jpeg,image/png,image/jpg,image/gif" onchange="previewCoverImage(event)">
                                            <small class="form-text text-muted">Format: JPG, JPEG, PNG, GIF. Maksimal 5MB. Klik untuk memilih foto.</small>
                                            
                                            <div id="coverPreviewContainer" class="mt-3" style="display: {{ ($catalogSections['cover']['cover_photo'] ?? ($company && $company->foto)) ? 'block' : 'none' }};">
                                                <p class="small mb-2 text-muted">Preview foto cover:</p>
                                                <div class="preview-image-wrapper">
                                                    @php
                                                        $coverSrc = '';
                                                        if (!empty($catalogSections['cover']['cover_photo'])) {
                                                            $coverSrc = ($company && $company->foto) ? asset('storage/'.$company->foto) : '';
                                                        } elseif ($company && $company->foto) {
                                                            $coverSrc = asset('storage/'.$company->foto);
                                                        }
                                                    @endphp
                                                    <img id="coverPreviewImage" src="{{ $coverSrc }}" alt="Preview" class="preview-img">
                                                    <button type="button" class="btn-remove-preview" onclick="removeCoverPreview()" title="Hapus foto">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TEAM SECTION EDITOR -->
                        <div class="section-editor mb-4" id="teamSection">
                            <div class="section-header">
                                <h6><i class="fas fa-users me-2"></i>Team Section</h6>
                                <small class="text-muted">Pimpinan dan petinggi perusahaan</small>
                            </div>
                            <div class="section-content">
                                <div class="form-group mb-3">
                                    <label>Judul Section</label>
                                    <input type="text" class="form-control" id="teamTitle" value="{{ $catalogSections['team']['title'] ?? 'THE TEAM.' }}" placeholder="THE TEAM.">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Deskripsi Team</label>
                                    <textarea class="form-control resizable-textarea" id="teamDescription" rows="3" placeholder="Deskripsi tentang tim...">{{ $catalogSections['team']['description'] ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' }}</textarea>
                                </div>
                                
                                <!-- Team Members -->
                                <div class="team-members-container">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label>Anggota Tim</label>
                                        <button type="button" class="btn btn-success btn-sm" id="addTeamMember" onclick="addTeamMemberRow()">
                                            <i class="fas fa-plus me-1"></i>Tambah Anggota
                                        </button>
                                    </div>
                                    <div id="teamMembersList">
                                        @php
                                            $savedMembers = $catalogSections['team']['members'] ?? [
                                                ['name' => 'Joko Susilo', 'position' => 'Direktur Utama', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'photo' => null],
                                                ['name' => 'Sari Wulandari', 'position' => 'Manajer Produksi', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'photo' => null],
                                            ];
                                        @endphp
                                        @foreach($savedMembers as $idx => $member)
                                        <div class="team-member-item" data-member-index="{{ $idx }}">
                                            <div class="row g-3 align-items-start mb-3">
                                                <div class="col-md-2">
                                                    <label class="form-label small">Foto</label>
                                                    <input type="file" class="form-control form-control-sm member-photo-input" accept="image/jpeg,image/png,image/jpg,image/gif" onchange="previewMemberImage(event, this)">
                                                    <div class="member-preview-container mt-2" style="{{ !empty($member['photo']) ? 'display: block;' : 'display: none;' }}">
                                                        <div class="member-preview-wrapper">
                                                            <img class="member-preview-img" src="{{ $member['photo'] ?? '' }}" alt="Preview">
                                                            <button type="button" class="btn-remove-member-preview" onclick="removeMemberPreview(this)" title="Hapus foto">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">Nama Lengkap</label>
                                                    <input type="text" class="form-control form-control-sm" placeholder="Nama Lengkap" value="{{ $member['name'] ?? '' }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small">Jabatan</label>
                                                    <input type="text" class="form-control form-control-sm" placeholder="Jabatan" value="{{ $member['position'] ?? '' }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small">Deskripsi Singkat</label>
                                                    <textarea class="form-control form-control-sm resizable-textarea" rows="2" placeholder="Deskripsi singkat...">{{ $member['description'] ?? '' }}</textarea>
                                                </div>
                                                <div class="col-md-1">
                                                    <label class="form-label small">&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-sm w-100 remove-member" onclick="removeTeamMemberRow(this)" style="display: none;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PRODUCTS SECTION EDITOR -->
                        <div class="section-editor mb-4" id="productsSection">
                            <div class="section-header">
                                <h6><i class="fas fa-box me-2"></i>Products Section</h6>
                                <small class="text-muted">Produk-produk perusahaan</small>
                            </div>
                            <div class="section-content">
                                <div class="form-group mb-3">
                                    <label>Judul Section</label>
                                    <input type="text" class="form-control" id="productsTitle" value="{{ $catalogSections['products']['title'] ?? 'PRODUCT MATERIAL.' }}" placeholder="PRODUCT MATERIAL.">
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Produk akan ditampilkan otomatis dari data produk yang sudah ada. 
                                    Anda dapat mengelola produk di menu <strong>Master Data > Produk</strong>.
                                </div>
                                @if($produks && $produks->count() > 0)
                                <div class="products-preview">
                                    <h6>Preview Produk ({{ $produks->count() }} produk)</h6>
                                    <div class="row">
                                        @foreach($produks->take(4) as $produk)
                                        <div class="col-md-3 mb-3">
                                            <div class="product-card">
                                                <div class="product-image">
                                                    @if($produk->foto)
                                                        <img src="{{ asset('storage/'.$produk->foto) }}" alt="{{ $produk->nama_produk }}">
                                                    @else
                                                        <div class="no-image">
                                                            <i class="fas fa-image"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="product-info">
                                                    <h6>{{ $produk->nama_produk }}</h6>
                                                    <p class="text-muted small">{{ Str::limit($produk->deskripsi ?: 'Tidak ada deskripsi', 50) }}</p>
                                                    <div class="product-price">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Belum ada produk yang tersedia. Silakan tambahkan produk terlebih dahulu.
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- LOCATION SECTION EDITOR -->
                        <div class="section-editor mb-4" id="locationSection">
                            <div class="section-header">
                                <h6><i class="fas fa-map-marker-alt me-2"></i>Location Section</h6>
                                <small class="text-muted">Lokasi dan kontak perusahaan</small>
                            </div>
                            <div class="section-content">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Judul Section</label>
                                            <input type="text" class="form-control" id="locationTitle" value="{{ $catalogSections['location']['title'] ?? 'LOKASI KAMI.' }}" placeholder="LOKASI KAMI.">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Nama Lokasi</label>
                                            <input type="text" class="form-control" id="locationName" value="{{ $catalogSections['location']['name'] ?? $company->nama ?? '' }}" placeholder="Nama Perusahaan">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Alamat</label>
                                            <textarea class="form-control resizable-textarea" id="locationAddress" rows="3" placeholder="Alamat lengkap...">{{ $catalogSections['location']['address'] ?? $company->alamat ?? '' }}</textarea>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Nomor Telepon</label>
                                            <input type="text" class="form-control" id="locationPhone" value="{{ $catalogSections['location']['phone'] ?? $company->telepon ?? '' }}" placeholder="Nomor telepon">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Email</label>
                                            <input type="email" class="form-control" id="locationEmail" value="{{ $catalogSections['location']['email'] ?? $company->email ?? '' }}" placeholder="Email perusahaan">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Link Google Maps</label>
                                            <input type="url" class="form-control" id="mapsLink" value="{{ $catalogSections['location']['maps_link'] ?? $company->maps_link ?? '' }}" placeholder="https://maps.google.com/...">
                                            <small class="text-muted">Paste link embed Google Maps</small>
                                        </div>
                                        <div class="map-preview">
                                            @if($company && $company->maps_link)
                                            <iframe src="{{ $company->maps_link }}" width="100%" height="200" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy"></iframe>
                                            @else
                                            <div class="no-map">
                                                <i class="fas fa-map"></i>
                                                <p>Preview peta akan muncul setelah link diisi</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SAVE BUTTON -->
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-success btn-lg px-5" id="saveAllCatalog">
                                <i class="fas fa-save me-2"></i>Update Semua Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.catalog-builder-container {
    max-width: 1200px;
    margin: 0 auto;
}

.section-editor {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 20px;
    background: #f8f9fa;
}

.section-header {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.section-header h6 {
    margin: 0;
    color: #495057;
    font-weight: 600;
}

.no-map {
    text-align: center;
    color: #6c757d;
    padding: 20px;
}

.no-map i {
    font-size: 2rem;
    margin-bottom: 10px;
    display: block;
}

.no-map p {
    margin: 0;
    font-size: 0.9rem;
}
    color: #6c757d;
}

.no-photo i, .no-map i {
    font-size: 2rem;
    margin-bottom: 10px;
    display: block;
}

.team-member-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    background: white;
    transition: all 0.3s ease;
}

.team-member-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.remove-member {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 8px 12px;
}

.remove-member:hover {
    transform: scale(1.05);
    background-color: #c82333 !important;
}

.remove-member i {
    font-size: 14px;
}

.product-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    background: white;
}

.product-image {
    height: 120px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-image .no-image {
    color: #6c757d;
}

.product-info {
    padding: 10px;
}

.product-info h6 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
}

.product-price {
    font-weight: 600;
    color: #007bff;
    font-size: 0.9rem;
}

.map-preview .no-map {
    height: 200px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.btn-xs {
    padding: 2px 6px;
    font-size: 0.75rem;
}

/* Resizable textarea styles */
.resizable-textarea {
    resize: both;
    min-height: 60px;
    max-height: 300px;
    min-width: 100%;
    overflow: auto;
}

/* Better textarea appearance */
.resizable-textarea:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Resize handle styling */
.resizable-textarea::-webkit-resizer {
    background: linear-gradient(-45deg, transparent 0px, transparent 2px, #007bff 2px, #007bff 4px, transparent 4px);
}

/* Preview Image Styling - Same as Produk Create */
.preview-image-wrapper {
    position: relative;
    display: inline-block;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.preview-img {
    max-height: 200px;
    max-width: 100%;
    width: auto;
    height: auto;
    object-fit: cover;
    display: block;
    border-radius: 8px;
}

.btn-remove-preview {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-remove-preview:hover {
    background: rgba(220, 53, 69, 1);
    transform: scale(1.1);
}

.btn-remove-preview i {
    font-size: 14px;
}

/* Member Photo Preview Styling */
.member-photo-container {
    width: 100%;
}

.member-preview-container {
    margin-top: 8px;
}

.member-preview-wrapper {
    position: relative;
    display: inline-block;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.member-preview-img {
    max-height: 120px;
    max-width: 120px;
    width: auto;
    height: auto;
    object-fit: cover;
    display: block;
    border-radius: 8px;
}

.btn-remove-member-preview {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-remove-member-preview:hover {
    background: rgba(220, 53, 69, 1);
    transform: scale(1.1);
}

.btn-remove-member-preview i {
    font-size: 12px;
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ===== GLOBAL VARIABLES =====
var teamMemberIndex = {{ count($catalogSections['team']['members'] ?? [['',''],['','']]) }}; // Start from saved member count

// ===== TEAM MEMBER FUNCTIONS (EXACTLY like in pembelian/create) =====
function addTeamMemberRow() {
    console.log('addTeamMemberRow called');
    const container = document.getElementById('teamMembersList');
    const newRow = document.createElement('div');
    newRow.className = 'team-member-item';
    newRow.setAttribute('data-member-index', teamMemberIndex);
    
    newRow.innerHTML = `
        <div class="row g-3 align-items-start mb-3">
            <div class="col-md-2">
                <label class="form-label small">Foto</label>
                <input type="file" class="form-control form-control-sm member-photo-input" accept="image/jpeg,image/png,image/jpg,image/gif" onchange="previewMemberImage(event, this)">
                <div class="member-preview-container mt-2" style="display: none;">
                    <div class="member-preview-wrapper">
                        <img class="member-preview-img" src="" alt="Preview">
                        <button type="button" class="btn-remove-member-preview" onclick="removeMemberPreview(this)" title="Hapus foto">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Nama Lengkap</label>
                <input type="text" class="form-control form-control-sm" placeholder="Nama Lengkap" value="">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Jabatan</label>
                <input type="text" class="form-control form-control-sm" placeholder="Jabatan" value="">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Deskripsi Singkat</label>
                <textarea class="form-control form-control-sm resizable-textarea" rows="2" placeholder="Deskripsi singkat..."></textarea>
            </div>
            <div class="col-md-1">
                <label class="form-label small">&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm w-100 remove-member" onclick="removeTeamMemberRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
    teamMemberIndex++;
    updateTeamRemoveButtons();
    
    console.log('Team member added. New count:', document.querySelectorAll('.team-member-item').length);
}

function removeTeamMemberRow(button) {
    console.log('removeTeamMemberRow called');
    const row = button.closest('.team-member-item');
    row.remove();
    updateTeamRemoveButtons();
    
    console.log('Team member removed. New count:', document.querySelectorAll('.team-member-item').length);
}

function updateTeamRemoveButtons() {
    const teamRows = document.querySelectorAll('.team-member-item');
    
    teamRows.forEach((row) => {
        const button = row.querySelector('.remove-member');
        if (button) {
            button.style.display = teamRows.length > 1 ? 'block' : 'none';
        }
    });
    
    console.log('Remove buttons updated. Total members:', teamRows.length);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== PAGE LOADED ===');
    console.log('addTeamMemberRow:', typeof addTeamMemberRow);
    console.log('removeTeamMemberRow:', typeof removeTeamMemberRow);
    console.log('Initial team count:', document.querySelectorAll('.team-member-item').length);
    updateTeamRemoveButtons();
});

// ===== JQUERY DOCUMENT READY =====
$(document).ready(function() {

    // Maps link preview
    $('#mapsLink').on('input', function() {
        const link = $(this).val().trim();
        if (link && (link.includes('google.com/maps') || link.includes('maps.google.com'))) {
            $('.map-preview').html(`<iframe src="${link}" width="100%" height="200" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy"></iframe>`);
        } else {
            $('.map-preview').html(`
                <div class="no-map">
                    <i class="fas fa-map"></i>
                    <p>Preview peta akan muncul setelah link diisi</p>
                </div>
            `);
        }
    });

    // Save all catalog data
    $('#saveAllCatalog').click(function() {
        const btn = $(this);
        const originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
        btn.prop('disabled', true);

        // Collect all data
        const catalogData = {
            cover: {
                company_name: $('#companyName').val().trim(),
                company_tagline: $('#companyTagline').val().trim(),
                company_description: $('#companyDescription').val().trim(),
                explore_text: $('#exploreText').val().trim(),
                cover_photo: $('#coverPreviewImage').attr('src') || ''
            },
            team: {
                title: $('#teamTitle').val().trim(),
                description: $('#teamDescription').val().trim(),
                members: []
            },
            products: {
                title: $('#productsTitle').val().trim()
            },
            location: {
                title: $('#locationTitle').val().trim(),
                name: $('#locationName').val().trim(),
                address: $('#locationAddress').val().trim(),
                phone: $('#locationPhone').val().trim(),
                email: $('#locationEmail').val().trim(),
                maps_link: $('#mapsLink').val().trim()
            }
        };

        // Collect team members
        $('.team-member-item').each(function() {
            const $item = $(this);
            const $photoImg = $item.find('.member-preview-img');
            const member = {
                name: $item.find('input[placeholder="Nama Lengkap"]').val().trim(),
                position: $item.find('input[placeholder="Jabatan"]').val().trim(),
                description: $item.find('textarea[placeholder="Deskripsi singkat..."]').val().trim(),
                photo: $photoImg.length > 0 && $photoImg.attr('src') ? $photoImg.attr('src') : ''
            };
            
            // Only add member if name is not empty
            if (member.name) {
                catalogData.team.members.push(member);
            }
        });

        console.log('Sending catalog data:', catalogData);

        // Send to server
        $.ajax({
            url: '{{ route("kelola-catalog.builder.save") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                sections: catalogData
            },
            success: function(response) {
                console.log('Server response:', response);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showCancelButton: true,
                        confirmButtonText: 'OK',
                        cancelButtonText: 'Lihat Catalog'
                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.cancel) {
                            // Open catalog page in new tab
                            window.open('{{ route("catalog") }}', '_blank');
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message || 'Terjadi kesalahan saat menyimpan data'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                let errorMessage = 'Gagal menyimpan data. Silakan coba lagi.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan!',
                    text: errorMessage
                });
            },
            complete: function() {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });
});

// ===== GLOBAL FUNCTIONS FOR PHOTO PREVIEW =====

// Compress image before upload
function compressImage(file, maxWidth = 800, maxHeight = 800, quality = 0.8) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function(event) {
            const img = new Image();
            img.src = event.target.result;
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                
                // Calculate new dimensions (more aggressive)
                if (width > height) {
                    if (width > maxWidth) {
                        height *= maxWidth / width;
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width *= maxHeight / height;
                        height = maxHeight;
                    }
                }
                
                canvas.width = width;
                canvas.height = height;
                
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                // Convert to base64 with compression
                const compressedBase64 = canvas.toDataURL('image/jpeg', quality);
                resolve(compressedBase64);
            };
            img.onerror = function() {
                reject(new Error('Failed to load image'));
            };
        };
        reader.onerror = function() {
            reject(new Error('Failed to read file'));
        };
    });
}

// Cover photo functions
function previewCoverImage(event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('coverPreviewContainer');
    const previewImage = document.getElementById('coverPreviewImage');
    
    if (file) {
        // Validasi ukuran file (max 5MB)
        if (file.size > 5242880) {
            Swal.fire('Error', 'Ukuran file terlalu besar! Maksimal 5MB.', 'error');
            event.target.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        // Validasi tipe file
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            Swal.fire('Error', 'Format file tidak valid! Gunakan JPG, JPEG, PNG, atau GIF.', 'error');
            event.target.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        // Compress and preview (smaller size and lower quality for cover)
        compressImage(file, 800, 800, 0.7).then(compressedBase64 => {
            previewImage.src = compressedBase64;
            previewContainer.style.display = 'block';
        }).catch(error => {
            console.error('Error compressing image:', error);
            Swal.fire('Error', 'Gagal memproses foto. Silakan coba lagi.', 'error');
        });
    } else {
        previewContainer.style.display = 'none';
    }
}

function removeCoverPreview() {
    const fileInput = document.getElementById('coverPhotoInput');
    const previewContainer = document.getElementById('coverPreviewContainer');
    
    fileInput.value = '';
    previewContainer.style.display = 'none';
}

// Member photo functions
function previewMemberImage(event, inputElement) {
    const file = event.target.files[0];
    const container = inputElement.closest('.col-md-2');
    const previewContainer = container.querySelector('.member-preview-container');
    const previewImage = container.querySelector('.member-preview-img');
    
    if (file) {
        // Validasi ukuran file (max 5MB)
        if (file.size > 5242880) {
            Swal.fire('Error', 'Ukuran file terlalu besar! Maksimal 5MB.', 'error');
            inputElement.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        // Validasi tipe file
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            Swal.fire('Error', 'Format file tidak valid! Gunakan JPG, JPEG, PNG, atau GIF.', 'error');
            inputElement.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        // Compress and preview (smaller size for team members)
        compressImage(file, 400, 400, 0.7).then(compressedBase64 => {
            previewImage.src = compressedBase64;
            previewContainer.style.display = 'block';
        }).catch(error => {
            console.error('Error compressing image:', error);
            Swal.fire('Error', 'Gagal memproses foto. Silakan coba lagi.', 'error');
        });
    } else {
        previewContainer.style.display = 'none';
    }
}

function removeMemberPreview(buttonElement) {
    const container = buttonElement.closest('.col-md-2');
    const fileInput = container.querySelector('.member-photo-input');
    const previewContainer = container.querySelector('.member-preview-container');
    
    fileInput.value = '';
    previewContainer.style.display = 'none';
}

</script>
@endpush