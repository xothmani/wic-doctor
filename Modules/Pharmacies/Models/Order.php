<?php
/*
 * File name: Order.php
 * Last modified: 2023.03.10 at 12:38:28
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Models;

use App\Casts\OptionCollectionCast;
use App\Casts\TaxCollectionCast;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Pharmacies\Casts\MedicineOptionCollectionCast;
use Modules\Pharmacies\Database\Factories\OrderFactory;
use Modules\Pharmacies\Events\OrderCreatingEvent;
use App\Models\Address;
use App\Models\Payment;
use App\Models\Tax;
use App\Models\User;
use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Order
 *
 * @property int id
 * @property User user
 * @property OrderStatus orderStatus
 * @property Payment payment
 * @property Pharmacy pharmacy
 * @property Medicine medicine
 * @property MedicineOption[] medicine_options
 * @property integer quantity
 * @property integer user_id
 * @property integer address_id
 * @property integer order_status_id
 * @property integer payment_status_id
 * @property Address address
 * @property integer payment_id
 * @property Tax[] taxes
 * @property Date order_at
 * @property string note
 * @property boolean cancel
 */
class Order extends Model
{

    use HasFactory;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'user_id' => 'required|exists:users,id',
        'order_status_id' => 'required|exists:order_statuses,id',
        'payment_id' => 'nullable|exists:payments,id'
    ];
    public $table = 'orders';
    public $fillable = [
        'pharmacy',
        'medicine',
        'medicine_options',
        'quantity',
        'user_id',
        'order_status_id',
        'address',
        'payment_id',
        'taxes',
        'order_at',
        'note',
        'cancel'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'pharmacy' => Pharmacy::class,
        'medicine' => Medicine::class,
        'medicine_options' => MedicineOptionCollectionCast::class,
        'address' => Address::class,
        'taxes' => TaxCollectionCast::class,
        'order_status_id' => 'integer',
        'payment_id' => 'integer',
        'quantity' => 'integer',
        'user_id' => 'integer',
        'order_at' => 'datetime:Y-m-d\TH:i:s.uP',
        'note' => 'string',
        'cancel' => 'boolean'
    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'creating' => OrderCreatingEvent::class,
        'updating' => OrderCreatingEvent::class,
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        return OrderFactory::new();
    }

    public function getCustomFieldsAttribute(): array
    {
        $hasCustomField = in_array(static::class, setting('custom_field_models', []));
        if (!$hasCustomField) {
            return [];
        }
        $array = $this->customFieldsValues()
            ->join('custom_fields', 'custom_fields.id', '=', 'custom_field_values.custom_field_id')
            ->where('custom_fields.in_table', '=', true)
            ->get()->toArray();

        return convertToAssoc($array, 'name');
    }

    public function customFieldsValues(): MorphMany
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    /**
     * @return BelongsTo
     **/
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return BelongsTo
     **/
    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id', 'id');
    }

    /**
     * @return BelongsTo
     **/
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id');
    }

    public function getTotal(): float
    {
        $total = $this->getSubtotal();
        $total += $this->getTaxesValue();
        return $total;
    }

    public function getSubtotal(): float
    {

        $total = $this->medicine->getPrice() * ($this->quantity >= 1 ? $this->quantity : 1);
        foreach ($this->medicine_options as $option) {
            $total += $option->price * ($this->quantity >= 1 ? $this->quantity : 1);
        }

        return $total;
    }

    public function getTaxesValue(): float
    {
        $total = $this->getSubtotal();
        $taxValue = 0;
        foreach ($this->taxes as $tax) {
            if ($tax->type == 'percent') {
                $taxValue += ($total * $tax->value / 100);
            } else {
                $taxValue += $tax->value;
            }
        }
        return $taxValue;
    }

}
