<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Kualifikasi;

class KualifikasiTargetResolver
{
    /**
     * Resolve Kualifikasi/Jabatan for a Pegawai
     *
     * @param Pegawai|null $pegawai
     * @return Kualifikasi|null
     */
    public function resolve(?Pegawai $pegawai): ?Kualifikasi
    {
        if (!$pegawai) {
            return null;
        }

        // Try to get from kualifikasiRelasi (new system)
        if ($pegawai->kualifikasiRelasi) {
            return $pegawai->kualifikasiRelasi;
        }

        // Query kualifikasi table
        $query = Kualifikasi::where('user_id', $pegawai->user_id ?? auth()->id());

        // Try by kualifikasi_id (if it exists in pegawai)
        if ($pegawai->kualifikasi_id) {
            $kualifikasi = (clone $query)->find($pegawai->kualifikasi_id);
            if ($kualifikasi) {
                return $kualifikasi;
            }
        }

        // Try by nama (match pegawai.kualifikasi string with kualifikasi.nama_kualifikasi)
        if (!empty($pegawai->kualifikasi)) {
            $kualifikasiByName = (clone $query)->where('nama_kualifikasi', $pegawai->kualifikasi)->first();
            if ($kualifikasiByName) {
                return $kualifikasiByName;
            }
        }

        // Fallback: Try match pegawai.jabatan string
        if (!empty($pegawai->jabatan)) {
            $kualifikasiByJabatan = (clone $query)->where('nama_kualifikasi', $pegawai->jabatan)->first();
            if ($kualifikasiByJabatan) {
                return $kualifikasiByJabatan;
            }
        }

        return null;
    }
}
