<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;

trait TracksCreator
{
    protected static function bootTracksCreator(): void
    {
        static::creating(function ($model) {

            // ✅ Jika ada user login, ambil ID dia
            if (Auth::check()) {
                $model->created_by = $model->created_by ?? Auth::id();
            } 
            // ✅ Jika tiada user login (contoh role tester atau seeding)
            else {
                // Ambil dari .env DEMO_USER_ID
                $demoId = env('DEMO_USER_ID');

                // Kalau ada DEMO_USER_ID, pakai itu; jika tiada, set 0
                $model->created_by = $model->created_by ?? ($demoId ? (int) $demoId : 0);
            }
        });
    }
}