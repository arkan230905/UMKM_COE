<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use \App\Traits\HasUserScope;
    protected $fillable = ['user_id', 'sub_district_id', 'street_address', 'label', 'is_default', 'latitude', 'longitude'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subDistrict(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class);
    }

    /**
     * Get complete formatted address
     */
    public function getFormattedAddress()
    {
        $subDistrict = $this->subDistrict;
        return $this->street_address . ', ' .
               $subDistrict->name . ', ' .
               $subDistrict->district->name . ', ' .
               $subDistrict->district->city->name . ', ' .
               $subDistrict->district->city->province->name;
    }

    /**
     * Get short address (street + subdistrict)
     */
    public function getShortAddress()
    {
        return $this->street_address . ', ' . $this->subDistrict->name;
    }
}
