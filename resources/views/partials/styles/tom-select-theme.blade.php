{{-- ============================================================ --}}
{{-- resources/views/partials/styles/tom-select-theme.blade.php  --}}
{{-- Custom theme untuk Tom Select dropdown                      --}}
{{-- ============================================================ --}}
<style id="ts-custom-theme">
/* ── Wrapper / Control ── */
.ts-wrapper { position: relative; }
.ts-wrapper .ts-control {
    background:   var(--bg-primary, #fff) !important;
    border:       1.5px solid var(--border-color, #e2e8f0) !important;
    border-radius: 8px !important;
    color:        var(--text-primary, #2d3748) !important;
    padding:      .52rem .85rem .52rem .85rem !important;
    font-size:    .875rem !important;
    min-height:   unset !important;
    box-shadow:   none !important;
    transition:   border-color .15s, box-shadow .15s !important;
    flex-wrap:    nowrap;
    gap:          4px;
    cursor:       pointer;
}
.ts-wrapper.focus .ts-control,
.ts-wrapper .ts-control:focus-within {
    border-color: var(--primary, #667eea) !important;
    box-shadow:   0 0 0 3px rgba(102,126,234,.16) !important;
}
.ts-wrapper.single .ts-control::after {
    content:'';
    border-color: var(--text-muted, #a0aec0) transparent transparent;
    border-style: solid;
    border-width: 5px 4px 0;
    position:     absolute;
    right:        .8rem;
    top:          50%;
    margin-top:   -2px;
    pointer-events: none;
}
.ts-wrapper .ts-control input {
    color:       var(--text-primary, #2d3748) !important;
    font-size:   .875rem !important;
    background:  transparent !important;
    min-width:   1px;
    padding:     0 !important;
    margin:      0 !important;
    flex:        1;
}
.ts-wrapper .ts-control input::placeholder { color: var(--text-muted, #a0aec0) !important; }
.ts-wrapper.single .ts-control .item { color: var(--text-primary, #2d3748) !important; font-size: .875rem; }

/* ── Dropdown ── */
.ts-dropdown {
    background:    var(--bg-primary, #fff) !important;
    border:        1.5px solid var(--border-color, #e2e8f0) !important;
    border-radius: 10px !important;
    box-shadow:    0 8px 32px rgba(0,0,0,.12), 0 2px 8px rgba(0,0,0,.06) !important;
    margin-top:    3px !important;
    overflow:      hidden !important;
    z-index:       9999 !important;
    font-size:     .875rem;
}
.ts-dropdown-content { max-height:230px; overflow-y:auto; padding:.3rem 0; }
.ts-dropdown-content::-webkit-scrollbar       { width: 4px; }
.ts-dropdown-content::-webkit-scrollbar-track  { background: transparent; }
.ts-dropdown-content::-webkit-scrollbar-thumb  { background: rgba(0,0,0,.15); border-radius: 4px; }
.ts-dropdown .option {
    color:       var(--text-primary, #2d3748) !important;
    font-size:   .875rem !important;
    padding:     .52rem .9rem !important;
    cursor:      pointer;
    transition:  background .1s;
    line-height: 1.4;
}
.ts-dropdown .option:hover,
.ts-dropdown .option.active  { background: rgba(102,126,234,.1) !important; color: #667eea !important; }
.ts-dropdown .option.selected { background: rgba(102,126,234,.14) !important; color: #667eea !important; font-weight: 700; }
.ts-no-result { padding:.7rem .9rem; color:var(--text-muted, #a0aec0); font-size:.84rem; text-align:center; }

.ts-wrapper.disabled .ts-control { background: var(--input-readonly-bg, #f7fafc) !important; opacity:.6; cursor:not-allowed !important; pointer-events:none; }

/* ── Dark Mode ── */
body.dark .ts-wrapper .ts-control { background:var(--bg-primary) !important; border-color:var(--border-color) !important; color:var(--text-primary) !important; }
body.dark .ts-wrapper.focus .ts-control { border-color:#667eea !important; box-shadow:0 0 0 3px rgba(102,126,234,.2) !important; }
body.dark .ts-wrapper .ts-control input { color: var(--text-primary) !important; }
body.dark .ts-wrapper.single .ts-control .item { color: var(--text-primary) !important; }
body.dark .ts-wrapper .ts-control::after { border-top-color: var(--text-muted) !important; }
body.dark .ts-dropdown { background:var(--bg-secondary, #2d3748) !important; border-color:var(--border-color) !important; box-shadow:0 8px 32px rgba(0,0,0,.4) !important; }
body.dark .ts-dropdown .option { color: var(--text-primary) !important; }
body.dark .ts-dropdown .option:hover,
body.dark .ts-dropdown .option.active   { background:rgba(102,126,234,.22) !important; color:#a5b4fc !important; }
body.dark .ts-dropdown .option.selected { background:rgba(102,126,234,.28) !important; color:#a5b4fc !important; }
body.dark .ts-wrapper.disabled .ts-control { background: var(--input-readonly-bg, #1a202c) !important; }
</style>