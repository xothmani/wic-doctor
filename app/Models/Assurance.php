<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Assurance extends Model
{
    use HasFactory;

    /**
     * Attributs modifiables en masse
     *
     * @var array
     */
    protected $fillable = [
        'nom',
        'description',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static array $rules = [
        'nom' => 'required|string|max:255',    // 'nom' est obligatoire
        'description' => 'nullable|string',   // 'description' est facultatif
    ];

public function getCustomFieldsAttribute(): array
{
    $hasCustomField = in_array(static::class, setting('custom_field_models', []));
    if (!$hasCustomField) {
        return [];
    }

    // Joindre les champs personnalisés à leurs valeurs et les retourner sous forme de tableau
    $array = $this->customFieldsValues()
        ->join('custom_fields', 'custom_fields.id', '=', 'custom_field_values.custom_field_id')
        ->where('custom_fields.in_table', '=', true)  // Filtrer les champs personnalisés devant être affichés dans le tableau
        ->get()->toArray();

    return convertToAssoc($array, 'name');  // Convertir en un tableau associatif avec le nom du champ comme clé
}

public function customFieldsValues(): MorphMany
{
    return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
}
    // Mutateur pour nettoyer le champ 'description'
    public function setDescriptionAttribute($value)
    {
        // Supprime les balises HTML du champ 'description'
        $this->attributes['description'] = strip_tags($value);
    }
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'assurance');
    }
    



}
