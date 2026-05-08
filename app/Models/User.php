<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role',
        'email',
        'nim',
        'nama',
        'prodi',
        'startup',
        'foto_base64',          // Nama file foto (bukan base64 string)
        'face_embedding',       // Float32Array 128-dim untuk pencocokan wajah
        'face_embedding_updated',
        'face_landmarks',       // JSON 68 titik landmark wajah referensi
        'google_authenticator_secret',
        'password_reset_token',
        'password_reset_expires',
        'work_start_date',
        'employment_start_date',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'password_hash',
        'remember_token',
        'google_authenticator_secret',
        // Data besar — hanya sertakan saat benar-benar dibutuhkan
        // (mis. endpoint khusus /api/face, bukan list user umum)
        'face_embedding',
        'face_landmarks',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Override serialization tanggal agar menggunakan timezone lokal (WIB) bukan UTC.
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
