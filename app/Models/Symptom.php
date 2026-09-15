<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Symptom extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function symptomReports(): BelongsToMany
    {
        return $this->belongsToMany(SymptomReport::class, 'symptom_report_symptom');
    }
}
