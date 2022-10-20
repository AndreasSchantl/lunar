<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\Address;
use Lunar\Base\Traits\CachesProperties;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Base\ValueObjects\Discount;
use Lunar\Base\ValueObjects\TaxBreakdown;
use Lunar\Database\Factories\CartFactory;
use Lunar\DataTypes\Price;
use Lunar\Managers\CartManager;

class Cart extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use HasMacros;
    use CachesProperties;

    /**
     * Array of cachable class properties.
     *
     * @var array
     */
    public $cachableProperties = [
        'total',
        'subTotal',
        'taxTotal',
        'discountTotal',
        'taxBreakdown',
        'shippingTotal',
        'discounts',
    ];

    /**
     * The cart manager.
     *
     * @var null|\Lunar\Managers\CartManager
     */
    protected ?CartManager $manager = null;

    /**
     * The cart total.
     *
     * @var null|\Lunar\DataTypes\Price
     */
    public ?Price $total = null;

    /**
     * The cart sub total.
     *
     * @var null|\Lunar\DataTypes\Price
     */
    public ?Price $subTotal = null;

    /**
     * The cart tax total.
     *
     * @var null|\Lunar\DataTypes\Price
     */
    public ?Price $taxTotal = null;

    /**
     * The discount total.
     *
     * @var null|\Lunar\DataTypes\Price
     */
    public ?Price $discountTotal = null;

    /**
     * All the tax breakdowns for the cart.
     *
     * @var null|Collection<TaxBreakdown>
     */
    public ?Collection $taxBreakdown;

    /**
     * The shipping total for the cart.
     *
     * @var null|Price
     */
    public ?Price $shippingTotal = null;

    /**
     * All the discounts for the cart.
     *
     * @var null|Collection<Discount>
     */
    public ?Collection $discounts;

    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Database\Factories\CartFactory
     */
    protected static function newFactory(): CartFactory
    {
        return CartFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'completed_at' => 'datetime',
        'meta' => 'object',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::retrieved(function ($cart) {
            $cart->restoreProperties();
        });
    }

    /**
     * Return the cart lines relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lines()
    {
        return $this->hasMany(CartLine::class, 'cart_id', 'id');
    }

    /**
     * Return the currency relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Return the user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function scopeUnmerged($query)
    {
        return $query->whereNull('merged_id');
    }

    /**
     * Return the addresses relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(CartAddress::class, 'cart_id');
    }

    /**
     * Return the shipping address relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function shippingAddress()
    {
        return $this->hasOne(CartAddress::class, 'cart_id')->whereType('shipping');
    }

    /**
     * Return the billing address relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function billingAddress()
    {
        return $this->hasOne(CartAddress::class, 'cart_id')->whereType('billing');
    }

    /**
     * Return the order relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Return the cart manager.
     *
     * @return \Lunar\Managers\CartManager
     */
    public function getManager()
    {
        return $this->manager ?? new CartManager($this);
    }

    /**
     * Set the cart manager.
     *
     * @var \Lunar\Managers\CartManager
     */
    public function setManager(CartManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Apply scope to get active cart.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereDoesntHave('order');
    }
}
