<?php

use Illuminate\Database\Eloquent\Model;
use Ritechoice23\Worldable\Models\Currency;
use Ritechoice23\Worldable\Traits\HasCurrency;

class TestProduct extends Model
{
    use HasCurrency;

    protected $table = 'products';

    protected $guarded = [];

    public $timestamps = false;
}

beforeEach(function () {
    Schema::create('products', function ($table) {
        $table->id();
        $table->string('name');
        $table->decimal('price', 10, 2);
    });

    $this->product = TestProduct::create([
        'name' => 'Test Product',
        'price' => 100.00,
    ]);

    $this->usd = Currency::create([
        'name' => 'US Dollar',
        'code' => 'USD',
        'symbol' => '$',
    ]);

    $this->ngn = Currency::create([
        'name' => 'Nigerian Naira',
        'code' => 'NGN',
        'symbol' => '₦',
    ]);
});

afterEach(function () {
    Schema::dropIfExists('products');
});

it('can attach a currency', function () {
    $this->product->attachCurrency('USD');

    expect($this->product->currencies)->toHaveCount(1)
        ->and($this->product->currencies->first()->code)->toBe('USD');
});

it('can attach currency by code', function () {
    $this->product->attachCurrency('usd');

    expect($this->product->currencies->first()->code)->toBe('USD');
});

it('can format money', function () {
    $this->product->attachCurrency('USD');

    $formatted = $this->product->formatMoney(100.50);

    expect($formatted)->toBe('$100.50');
});

it('can get currency symbol accessor', function () {
    $this->product->attachCurrency('NGN');

    expect($this->product->currency_symbol)->toBe('₦');
});

it('can filter products priced in a currency', function () {
    $product2 = TestProduct::create(['name' => 'Product 2', 'price' => 200]);

    $this->product->attachCurrency('USD');
    $product2->attachCurrency('NGN');

    $usdProducts = TestProduct::pricedIn('USD')->get();

    expect($usdProducts)->toHaveCount(1)
        ->and($usdProducts->first()->name)->toBe('Test Product');
});

it('can handle multiple currencies with groups', function () {
    $this->product->attachCurrency('USD', 'display');
    $this->product->attachCurrency('NGN', 'base');

    $displayCurrency = $this->product->currencies()->wherePivot('group', 'display')->first();
    $baseCurrency = $this->product->currencies()->wherePivot('group', 'base')->first();

    expect($displayCurrency->code)->toBe('USD')
        ->and($baseCurrency->code)->toBe('NGN');
});

it('formats money with specific group', function () {
    $this->product->attachCurrency('USD', 'display');
    $this->product->attachCurrency('NGN', 'base');

    $formatted = $this->product->formatMoney(100, 'base');

    expect($formatted)->toBe('₦100.00');
});
