<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ship extends Model
{
    use HasFactory;

    protected $table = 'ship';
    protected $fillable = [
        'user_id',
        'kode_kapal',
        'nama_kapal',
        'nama_pemilik',
        'alamat_pemilik',
        'nomor_izin',
        'ukuran_kapal',
        'kapten',
        'jumlah_anggota',
        'foto_kapal',
        'nomor_izin',
        'dokumen_perizinan',
        'status',
        'notes'
    ];


    /**
     * Get all of the user for the Ship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
