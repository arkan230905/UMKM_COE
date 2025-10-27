<div class="sidebar bg-dark text-white vh-100 p-3">
    <h4 class="text-center mb-4">Menu</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a href="{{ route('dashboard') }}" class="nav-link text-white">Dashboard</a>
        </li>

        <!-- MASTER DATA -->
        <li class="nav-item mb-2">
            <h6 class="text-uppercase text-muted mt-3 mb-2" style="font-size: 0.75rem;">Master Data</h6>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.aset.index') }}" class="nav-link text-white">Aset</a>
        </li>
    </ul>
</div>
