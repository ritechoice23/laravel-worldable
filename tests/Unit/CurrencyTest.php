<?php

use Ritechoice23\Worldable\Models\Currency;

beforeEach(function () {
    $this->currency = Currency::create([
        'name' => 'Nigerian Naira',
        'code' => 'NGN',
        'symbol' => '₦',
    ]);
});

it('can create a currency', function () {
    expect($this->currency)->toBeInstanceOf(Currency::class)
        ->and($this->currency->name)->toBe('Nigerian Naira')
        ->and($this->currency->code)->toBe('NGN')
        ->and($this->currency->symbol)->toBe('₦');
});

it('can find currency by code', function () {
    $found = Currency::whereCode('NGN')->first();

    expect($found)->not->toBeNull()
        ->and($found->name)->toBe('Nigerian Naira');
});

it('can find currency by code case-insensitive', function () {
    $found = Currency::whereCode('ngn')->first();

    expect($found)->not->toBeNull()
        ->and($found->code)->toBe('NGN');
});

it('can format money', function () {
    $formatted = $this->currency->format(5000.50);

    expect($formatted)->toBe('₦5,000.50');
});

it('uses code as fallback when symbol is missing', function () {
    $currency = Currency::create([
        'name' => 'Test Currency',
        'code' => 'TST',
    ]);

    $formatted = $currency->format(100);

    expect($formatted)->toContain('TST');
});
