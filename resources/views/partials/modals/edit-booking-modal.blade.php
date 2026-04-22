{{-- ============================================================ --}}
{{-- resources/views/partials/modals/edit-booking-modal.blade.php --}}
{{-- ============================================================ --}}
<div class="modal-overlay" id="editBookingModal">
    <div class="modal-content" style="max-width:550px;">
        <span class="modal-close" id="closeEditBookingModal">✖</span>
        <h2 class="modal-header">✏️ Edit Booking</h2>

        <form id="editBookingForm">
            <input type="hidden" id="editBookingId">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Payment Status</label>
                    <select id="editPaymentStatus" class="form-select">
                        <option value="unpaid">❌ Unpaid</option>
                        <option value="partial">⚡ Partial</option>
                        <option value="paid">✅ Paid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Deposit Status</label>
                    <select id="editDepositStatus" class="form-select">
                        <option value="not_paid">❌ Not Paid</option>
                        <option value="paid">✅ Paid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Booking Status</label>
                    <select id="editBookingStatus" class="form-select">
                        <option value="pending">🕐 Pending</option>
                        <option value="confirmed">✅ Confirmed</option>
                        <option value="cancelled">❌ Cancelled</option>
                        <option value="completed">🏁 Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Total Amount (RM)</label>
                    <input id="editTotalAmount" type="number" step="0.01" class="form-input">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Notes</label>
                    <textarea id="editBookingNotes" rows="3" class="form-input"></textarea>
                </div>
            </div>
            <div style="text-align:center;margin-top:1.5rem;">
                <button type="submit" class="btn btn-success btn-lg">💾 Update Booking</button>
            </div>
        </form>
    </div>
</div>