<?php

namespace App\Observers;

use App\Http\Controllers\LaporanController;

class ConversionConsistencyObserver
{
    /**
     * Handle the model "created" event.
     * Ensures conversion consistency when new items are created
     */
    public function created($model)
    {
        $this->ensureConversionConsistency($model);
    }

    /**
     * Handle the model "updated" event.
     * Ensures conversion consistency when items are updated
     */
    public function updated($model)
    {
        $this->ensureConversionConsistency($model);
    }

    /**
     * Ensure conversion consistency for the model
     * 
     * @param mixed $model
     * @return void
     */
    private function ensureConversionConsistency($model)
    {
        // Check if model has conversion fields
        if (!$this->hasConversionFields($model)) {
            return;
        }

        $updated = false;
        
        // Sync sub_satuan_1: If nilai > 0 and konversi <= 0, copy nilai to konversi
        if ($this->shouldSyncConversion($model, 1)) {
            $model->sub_satuan_1_konversi = $model->sub_satuan_1_nilai;
            $updated = true;
        }
        
        // Sync sub_satuan_2: If nilai > 0 and konversi <= 0, copy nilai to konversi
        if ($this->shouldSyncConversion($model, 2)) {
            $model->sub_satuan_2_konversi = $model->sub_satuan_2_nilai;
            $updated = true;
        }
        
        // Sync sub_satuan_3: If nilai > 0 and konversi <= 0, copy nilai to konversi
        if ($this->shouldSyncConversion($model, 3)) {
            $model->sub_satuan_3_konversi = $model->sub_satuan_3_nilai;
            $updated = true;
        }
        
        // Save if any changes were made (without triggering observer again)
        if ($updated) {
            $model->saveQuietly();
        }
    }

    /**
     * Check if model has conversion fields
     * 
     * @param mixed $model
     * @return bool
     */
    private function hasConversionFields($model)
    {
        return isset($model->sub_satuan_1_konversi) || 
               isset($model->sub_satuan_1_nilai) ||
               method_exists($model, 'getTable') && 
               in_array($model->getTable(), ['bahan_bakus', 'bahan_pendukungs', 'produks']);
    }

    /**
     * Check if conversion should be synced for specific sub satuan
     * 
     * @param mixed $model
     * @param int $subSatuanNumber
     * @return bool
     */
    private function shouldSyncConversion($model, $subSatuanNumber)
    {
        $idField = "sub_satuan_{$subSatuanNumber}_id";
        $nilaiField = "sub_satuan_{$subSatuanNumber}_nilai";
        $konversiField = "sub_satuan_{$subSatuanNumber}_konversi";
        
        // Only sync if:
        // 1. Sub satuan ID exists (sub unit is configured)
        // 2. Nilai field has a valid value (> 0)
        // 3. Konversi field is empty or invalid (<= 0)
        return isset($model->$idField) && $model->$idField > 0 &&
               isset($model->$nilaiField) && $model->$nilaiField > 0 &&
               (!isset($model->$konversiField) || $model->$konversiField <= 0);
    }
}