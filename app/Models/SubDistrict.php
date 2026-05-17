<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubDistrict extends Model
{
    protected $fillable = ['district_id', 'code', 'name', 'latitude', 'longitude'];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function customerAddresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    /**
     * Get full address path: Province > City > District > SubDistrict
     */
    public function getFullAddressPath()
    {
        return $this->district->city->province->name . ' > ' .
               $this->district->city->name . ' > ' .
               $this->district->name . ' > ' .
               $this->name;
    }
}
