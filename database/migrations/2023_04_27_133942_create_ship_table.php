<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ship', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kapal');
            $table->string('nama_kapal');
            $table->string("nama_pemilik");
            $table->text("alamat_pemilik");
            $table->string("ukuran_kapal");
            $table->string("kapten");
            $table->integer("jumlah_anggota");
            $table->string("foto_kapal");
            $table->string("nomor_izin");
            $table->string("dokumen_perizinan");
            $table->enum('status', ["unverified", "verified", "rejected"])->default("unverified");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ship');
    }
};
