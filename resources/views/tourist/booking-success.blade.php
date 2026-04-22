<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tempahan Berjaya! 🎉</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0f2027; --teal: #00d4aa; --teal-dark: #00b894; --mid: #2c5364;
            --bg: #f0f4f8; --text: #2d3748; --muted: #718096; --border: #e2e8f0;
            --radius-sm: 8px; --radius-md: 12px; --radius-lg: 18px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:var(--bg); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem 1rem; }

        @keyframes bounceIn { 0%{transform:scale(0.4);opacity:0} 60%{transform:scale(1.12)} 80%{transform:scale(0.96)} 100%{transform:scale(1);opacity:1} }
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        @keyframes confetti { 0%{transform:translateY(0) rotate(0);opacity:1} 100%{transform:translateY(-80px) rotate(360deg);opacity:0} }

        .card {
            background:white; border-radius:var(--radius-lg); padding:2rem 1.75rem;
            max-width:520px; width:100%;
            box-shadow:0 20px 60px rgba(0,0,0,0.12);
            text-align:center;
            animation:fadeUp .5s ease both;
        }

        .success-wrap { position:relative; margin-bottom:1.1rem; }
        .success-icon { font-size:4.5rem; display:block; animation:bounceIn .7s cubic-bezier(.34,1.56,.64,1) both; }
        .confetti-piece {
            position:absolute; font-size:1.2rem; pointer-events:none;
            animation:confetti 1.2s ease-out both;
        }

        h1 { font-size:1.65rem; font-weight:800; color:var(--text); margin-bottom:0.4rem; }
        .subtitle { color:var(--muted); font-size:0.88rem; margin-bottom:1.5rem; line-height:1.6; max-width:360px; margin-left:auto; margin-right:auto; }

        /* Booking Number */
        .booking-no-box {
            background:linear-gradient(135deg,var(--navy),var(--mid));
            color:white; border-radius:var(--radius-md); padding:1.1rem 1.25rem;
            margin-bottom:1.25rem; position:relative; overflow:hidden;
        }
        .booking-no-box::before { content:'✈️'; position:absolute; right:-10px; top:50%; transform:translateY(-50%); font-size:4rem; opacity:0.08; }
        .booking-no-label { font-size:0.72rem; color:rgba(255,255,255,0.65); margin-bottom:0.3rem; text-transform:uppercase; letter-spacing:.08em; }
        .booking-no-val { font-size:1.7rem; font-weight:800; color:var(--teal); font-family:monospace; letter-spacing:1px; }

        /* Info Grid */
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:0.65rem; margin-bottom:1.25rem; }
        .info-item { background:var(--bg); border-radius:var(--radius-sm); padding:0.75rem 0.9rem; text-align:left; }
        .info-label { font-size:0.67rem; color:var(--muted); font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin-bottom:0.2rem; }
        .info-val { font-size:0.88rem; font-weight:700; color:var(--text); line-height:1.3; }

        /* WA Box */
        .wa-box { background:#f0fdf9; border:1.5px solid var(--teal); border-radius:var(--radius-md); padding:1.1rem; margin-bottom:1.25rem; text-align:left; }
        .wa-box h3 { color:#065f46; font-size:0.92rem; font-weight:700; margin-bottom:0.35rem; }
        .wa-box p { color:#047857; font-size:0.82rem; margin-bottom:0.75rem; line-height:1.5; }


        /* Notice */
        .notice { font-size:0.76rem; color:#a0aec0; margin-top:1.35rem; line-height:1.7; padding:0.9rem; background:var(--bg); border-radius:var(--radius-sm); text-align:left; }
        .notice strong { color:var(--muted); }

        /* Steps */
        .next-steps { margin-top:1.1rem; text-align:left; }
        .next-steps-title { font-size:0.78rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; margin-bottom:0.65rem; }
        .step-item { display:flex; gap:0.65rem; align-items:flex-start; margin-bottom:0.5rem; }
        .step-num { width:22px; height:22px; border-radius:50%; background:linear-gradient(135deg,var(--navy),var(--mid)); color:white; font-size:0.68rem; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px; }
        .step-text { font-size:0.8rem; color:var(--muted); line-height:1.5; }
        /* Buttons */
.btn-row { display:flex; flex-direction:column; gap:0.6rem; margin-top:1.25rem; }
.btn {
    display:inline-flex; align-items:center; justify-content:center; gap:0.5rem;
    padding:0.75rem 1.5rem; border-radius:var(--radius-sm); font-family:'Poppins',sans-serif;
    font-size:0.875rem; font-weight:600; text-decoration:none; cursor:pointer;
    border:none; transition:all .2s ease; width:100%;
}
.btn-ghost {
    background:var(--bg); color:var(--text);
    border:1.5px solid var(--border);
}
.btn-ghost:hover { background:var(--border); transform:translateY(-1px); }

.btn-wa {
    display:inline-flex; align-items:center; justify-content:center; gap:0.5rem;
    width:100%; padding:0.75rem 1.5rem; border-radius:var(--radius-sm);
    background:linear-gradient(135deg,#25d366,#128c7e); color:white;
    font-family:'Poppins',sans-serif; font-size:0.875rem; font-weight:700;
    text-decoration:none; border:none; cursor:pointer;
    transition:all .2s ease; box-shadow:0 4px 12px rgba(37,211,102,0.3);
}
.btn-wa:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(37,211,102,0.4); }
.btn-wa:active { transform:translateY(0); }

.btn-secondary {
    display:inline-flex; align-items:center; justify-content:center; gap:0.5rem;
    width:100%; padding:0.75rem 1.5rem; border-radius:var(--radius-sm);
    background:white; color:#065f46;
    border:1.5px solid var(--teal); font-family:'Poppins',sans-serif;
    font-size:0.875rem; font-weight:600; text-decoration:none;
    transition:all .2s ease;
}
.btn-secondary:hover { background:#f0fdf9; transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,212,170,0.2); }

        @media(max-width:480px) {
            body { padding:1rem 0.85rem; align-items:flex-start; }
            .card { padding:1.5rem 1.25rem; border-radius:var(--radius-md); }
            h1 { font-size:1.4rem; }
            .success-icon { font-size:3.8rem; }
            .booking-no-val { font-size:1.4rem; }
            .info-grid { grid-template-columns:1fr; }
            .btn-row .btn { max-width:none; }
        }
    </style>
</head>
<body>
<div class="card">
    <div class="success-wrap">
        <span class="success-icon" id="successIcon">🎉</span>
    </div>
    <h1>Tempahan Berjaya!</h1>
    <p class="subtitle">Terima kasih! Tempahan anda telah diterima. Admin akan menghubungi anda tidak lama lagi untuk pengesahan.</p>

    <div class="booking-no-box">
        <div class="booking-no-label">No. Tempahan Anda</div>
        <div class="booking-no-val">{{ $booking->booking_no }}</div>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Trip</div>
            <div class="info-val">{{ $booking->trip->location ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Syarikat</div>
            <div class="info-val">{{ $booking->trip->company->name ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Tarikh Berlepas</div>
            <div class="info-val">{{ $booking->trip->date_from ? \Carbon\Carbon::parse($booking->trip->date_from)->format('d/m/Y') : '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Jumlah Bayaran</div>
            <div class="info-val" style="color:var(--teal);">RM {{ number_format($booking->total_amount,2) }}</div>
        </div>
    </div>

    {{-- WhatsApp Section --}}
    @if(isset($waResult))
        @if($waResult['method']==='fonnte' && $waResult['success'])
            <div class="wa-box">
                <h3>📲 Notifikasi WhatsApp Dihantar!</h3>
                <p>Admin telah diberitahu mengenai tempahan anda melalui WhatsApp secara automatik.</p>
            </div>
        @else
            <div class="wa-box">
                <h3>📲 Hubungi Admin Via WhatsApp</h3>
                <p>Klik butang di bawah untuk menghantar butiran tempahan kepada admin.</p>
                <a href="{{ $waAdminLink }}" target="_blank" class="btn btn-wa" style="font-size:0.85rem;">
                    💬 Hantar ke WhatsApp Admin
                </a>
            </div>
        @endif
    @endif

    <!-- Next Steps -->
    <div class="next-steps">
        <div class="next-steps-title">Langkah Seterusnya</div>
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-text">Simpan no. booking <strong style="color:var(--text);">{{ $booking->booking_no }}</strong> sebagai rujukan</div>
        </div>
        
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-text">Tunggu admin hubungi anda untuk proses bayaran</div>
        </div>
    </div>
 <div class="btn-row">
    @php
        $waAdmin = app(\App\Services\WhatsAppService::class)
            ->getAdminWaLink(
                'Salam Admin, saya ingin bertanya tentang tempahan saya.' . "\n\n" .
                ' No. Booking: *' . $booking->booking_no . '*' . "\n" .
                ' Trip: ' . ($booking->trip->location ?? '-') . "\n" .
                ' Tarikh: ' . ($booking->trip->date_from ? \Carbon\Carbon::parse($booking->trip->date_from)->format('d/m/Y') : '-') . "\n" .
                ' Jumlah: RM ' . number_format($booking->total_amount, 2)
            );
    @endphp

    <a href="{{ $waAdmin }}" target="_blank" class="btn btn-wa">
        💬 Hubungi Admin via WhatsApp
    </a>

    <a href="{{ route('tourist.dashboard') }}" class="btn btn-ghost">
        🏠 Kembali ke Dashboard
    </a>
</div>

    <div class="notice">
        ⚠️ <strong>Status booking anda: Menunggu Pengesahan.</strong><br>
        Admin akan menghubungi anda untuk proses pembayaran deposit dan pengesahan lanjut. Proses ini biasanya mengambil masa 1–2 hari bekerja.
    </div>
</div>

<script>
// Confetti animation
var confetti=['🎊','🎉','✨','🌟','💫'];
var icon=document.getElementById('successIcon');
setTimeout(function(){
    for(var i=0;i<8;i++){
        (function(idx){
            setTimeout(function(){
                var el=document.createElement('span');
                el.className='confetti-piece';
                el.textContent=confetti[idx%confetti.length];
                el.style.cssText='left:'+(20+Math.random()*60)+'%;top:'+(10+Math.random()*30)+'%;animation-delay:'+(Math.random()*.5)+'s;animation-duration:'+(0.8+Math.random()*.6)+'s;';
                document.querySelector('.success-wrap').appendChild(el);
                setTimeout(function(){el.remove();},1500);
            },idx*120);
        })(i);
    }
},400);
</script>
</body>
</html>