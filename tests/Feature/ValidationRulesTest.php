<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use Ritechoice23\Worldable\Models\City;
use Ritechoice23\Worldable\Models\Country;
use Ritechoice23\Worldable\Models\Currency;
use Ritechoice23\Worldable\Models\Language;
use Ritechoice23\Worldable\Rules\ValidCity;
use Ritechoice23\Worldable\Rules\ValidCountry;
use Ritechoice23\Worldable\Rules\ValidCurrency;
use Ritechoice23\Worldable\Rules\ValidLanguage;

beforeEach(function () {
    Country::create([
        'name' => 'Nigeria',
        'iso_code' => 'NG',
        'iso_code_3' => 'NGA',
        'continent_id' => 1,
    ]);

    Currency::create([
        'name' => 'US Dollar',
        'code' => 'USD',
        'symbol' => '$',
    ]);

    $state = \Ritechoice23\Worldable\Models\State::create([
        'name' => 'Lagos',
        'code' => 'LA',
        'country_id' => 1,
    ]);

    City::create([
        'name' => 'Lagos',
        'state_id' => $state->id,
    ]);

    Language::create([
        'name' => 'English',
        'iso_code' => 'en',
        'native_name' => 'English',
    ]);
});

it('validates country by name', function () {
    $validator = Validator::make([
        'country' => 'Nigeria',
    ], [
        'country' => [new ValidCountry],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('validates country by ISO code', function () {
    $validator = Validator::make([
        'country' => 'NG',
    ], [
        'country' => [new ValidCountry],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('validates country by ISO 3 code', function () {
    $validator = Validator::make([
        'country' => 'NGA',
    ], [
        'country' => [new ValidCountry],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('fails validation for invalid country', function () {
    $validator = Validator::make([
        'country' => 'InvalidCountry',
    ], [
        'country' => [new ValidCountry],
    ]);

    expect($validator->fails())->toBeTrue();
});

it('validates currency by code', function () {
    $validator = Validator::make([
        'currency' => 'USD',
    ], [
        'currency' => [new ValidCurrency],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('validates currency by name', function () {
    $validator = Validator::make([
        'currency' => 'US Dollar',
    ], [
        'currency' => [new ValidCurrency],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('fails validation for invalid currency', function () {
    $validator = Validator::make([
        'currency' => 'InvalidCurrency',
    ], [
        'currency' => [new ValidCurrency],
    ]);

    expect($validator->fails())->toBeTrue();
});

it('validates city by name', function () {
    $validator = Validator::make([
        'city' => 'Lagos',
    ], [
        'city' => [new ValidCity],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('fails validation for invalid city', function () {
    $validator = Validator::make([
        'city' => 'InvalidCity',
    ], [
        'city' => [new ValidCity],
    ]);

    expect($validator->fails())->toBeTrue();
});

it('validates language by name', function () {
    $validator = Validator::make([
        'language' => 'English',
    ], [
        'language' => [new ValidLanguage],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('validates language by ISO code', function () {
    $validator = Validator::make([
        'language' => 'en',
    ], [
        'language' => [new ValidLanguage],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('fails validation for invalid language', function () {
    $validator = Validator::make([
        'language' => 'InvalidLanguage',
    ], [
        'language' => [new ValidLanguage],
    ]);

    expect($validator->fails())->toBeTrue();
});

it('allows custom validation messages', function () {
    $validator = Validator::make([
        'country' => 'InvalidCountry',
    ], [
        'country' => [(new ValidCountry)->withMessage('Please select a valid country')],
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('country'))->toBe('Please select a valid country');
});
