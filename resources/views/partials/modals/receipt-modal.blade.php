{{-- ============================================================ --}}
{{-- resources/views/partials/modals/receipt-modal.blade.php     --}}
{{-- Modal: View & Download Receipt                              --}}
{{-- ============================================================ --}}
<div class="modal-overlay" id="receiptModal">
    <div class="modal-content" style="max-width:800px;">
        <span class="modal-close" id="closeReceiptModal">✖</span>
        <div class="receipt-container" id="receiptContent">
            <div class="receipt-header">
                <div class="receipt-title">PAYMENT RECEIPT</div>
                <div class="receipt-subtitle">Arson Trip System</div>
                <div style="margin-top:.5rem;color:var(--text-muted);font-size:.85rem;" id="receiptDateDisplay"></div>
            </div>
            <div id="receiptBody"></div>
            <div class="receipt-footer">
                <p>Thank you for your payment!</p>
                <p style="margin-top:.5rem;">This is a computer-generated receipt</p>
            </div>
        </div>
        <div style="text-align:center;margin-top:2rem;">
            <button id="printReceiptBtn" class="btn btn-primary btn-lg">📄 Download PDF Receipt</button>
        </div>
    </div>
</div>