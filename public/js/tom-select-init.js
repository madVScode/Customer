// ================================================================
// TOM SELECT v2 — Booking & Receipt Dropdowns
// Whitelist: company, trip, customer untuk booking & receipt
// ================================================================
;(function($) {
    'use strict';

    var WHITELIST = {
        // ── Booking ───────────────────────────────────────────
        bookingCompanySelect  : 1,
        bookingTripSelect     : 1,
        bookingCustomerSelect : 1,
        // ── Receipt ───────────────────────────────────────────
        receiptCompanySelect  : 1,
        receiptTripSelect     : 1,
        receiptCustomerSelect : 1,
    };

    window.tsInstances = window.tsInstances || {};
    var _instances = window.tsInstances;

    function initOne(el) {
        if (!el || el.tagName !== 'SELECT') return;
        if (!el.id || !WHITELIST[el.id])    return;
        if (el._tsInited)                   return;
        el._tsInited = true;

        try {
            var inst = new TomSelect(el, {
                maxOptions       : 800,
                searchField      : ['text'],
                allowEmptyOption : true,
                selectOnTab      : true,
                placeholder      : el.getAttribute('placeholder') || '-- Pilih --',
                render: {
                    no_results: function(data, escape) {
                        return '<div class="ts-no-result">🔍 Tiada hasil untuk "' + escape(data.input) + '"</div>';
                    },
                    option: function(data, escape) {
                        return '<div class="ts-option">' + escape(data.text) + '</div>';
                    }
                },
                onInitialize: function() {
                    if (el.disabled) this.disable();
                }
            });
            _instances[el.id] = inst;
        } catch(e) {
            el._tsInited = false;
            console.warn('TomSelect init failed for #' + el.id, e);
        }
    }

    function initAll(root) {
        (root || document).querySelectorAll('select').forEach(function(el) {
            if (el.id && WHITELIST[el.id] && !el._tsInited) initOne(el);
        });
    }

    function watchNewSelects() {
        var obs = new MutationObserver(function(muts) {
            muts.forEach(function(m) {
                m.addedNodes.forEach(function(node) {
                    if (!node || node.nodeType !== 1) return;
                    if (node.tagName === 'SELECT' && node.id && WHITELIST[node.id] && !node._tsInited) {
                        setTimeout(function() { initOne(node); }, 0);
                    }
                    if (node.querySelectorAll) {
                        node.querySelectorAll('select').forEach(function(sel) {
                            if (sel.id && WHITELIST[sel.id] && !sel._tsInited) {
                                setTimeout(function() { initOne(sel); }, 0);
                            }
                        });
                    }
                });
            });
        });
        obs.observe(document.body, { childList: true, subtree: true });
    }

    // ── Global API ────────────────────────────────────────────

    window.tsSetValue = function(id, val, silent) {
        var inst = _instances[id];
        if (!inst) return;
        inst.setValue(val, silent === true);
    };

    window.tsClear = function(id) {
        var inst = _instances[id];
        if (!inst) return;
        inst.clear(true);
        inst.clearOptions();
    };

    window.tsEnable = function(id) {
        var inst = _instances[id];
        if (inst) inst.enable();
    };

    window.tsDisable = function(id) {
        var inst = _instances[id];
        if (inst) inst.disable();
    };

    // ✅ tsUpdate — populate options + set state
    window.tsUpdate = function(id, options, placeholder, enable) {
        var inst = _instances[id];
        if (!inst) return;
        inst.clear(true);
        inst.clearOptions();
        if (placeholder) inst.addOption({ value: '', text: placeholder });
        options.forEach(function(o) {
            inst.addOption({ value: String(o.value), text: o.text });
        });
        inst.refreshOptions(false);
        inst.setValue('', true);
        enable ? inst.enable() : inst.disable();
    };

    // ── Bootstrap ─────────────────────────────────────────────
    function boot() {
        initAll();
        watchNewSelects();
        console.log('✅ TomSelect ready — ' + Object.keys(_instances).length + ' instance(s)');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

})(typeof jQuery !== 'undefined' ? jQuery : null);