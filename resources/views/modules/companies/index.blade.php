{{-- ============================================================ --}}
{{-- resources/views/modules/companies/index.blade.php           --}}
{{-- View: Senarai & Urus Company                                --}}
{{-- ============================================================ --}}
@extends('layouts.app')
@section('title', 'Companies')
@section('content')
<div id="companiesView">
    <h1 class="page-title">🏢 Companies</h1>
    <div class="content-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
            <h2 style="font-size:1.5rem;font-weight:600;color:var(--text-primary);margin:0;">Manage Companies</h2>
            <button id="addCompanyBtn" class="btn btn-success">➕ Add Company</button>
        </div>
        <div id="companiesGrid"
             style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.5rem;"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    window.loadCompanies && window.loadCompanies();
});
</script>
@endpush