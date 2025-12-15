# Currencies

## Setup

```php
use Ritechoice23\Worldable\Traits\HasCurrency;

class Product extends Model {
    use HasCurrency;
}
```

## Attach Currency

```php
$product->attachCurrency('USD');
$product->attachCurrency('NGN');
$product->attachCurrency('usd');  // Case insensitive
```

## Money Formatting

```php
$product->attachCurrency('USD');

// Basic
echo $product->formatMoney(99.99);  // "$99.99"

// Custom decimals
echo $product->formatMoney(99.99, 0);  // "$100"
echo $product->formatMoney(99.99, 3);  // "$99.990"

// With group
$product->attachCurrency('EUR', 'display');
echo $product->formatMoney(50, null, 'display');  // "€50.00"

// Locale-aware
echo $product->formatMoney(1234.56, null, null, 'de_DE');  // "1.234,56 $"
echo $product->formatMoney(1234.56, null, null, 'en_US');  // "$1,234.56"

// Negative amounts
echo $product->formatMoney(-50);  // "-$50.00"
```

## Currency Model

```php
use Ritechoice23\Worldable\Models\Currency;

$currency = Currency::where('code', 'USD')->first();

// Format directly
echo $currency->format(1000);  // "$1,000.00"
echo $currency->format(1000, 0);  // "$1,000"
echo $currency->format(1000, 2, true);  // "$1,000.00" (no space)
echo $currency->format(1000, 2, false);  // "$ 1,000.00" (with space)
echo $currency->format(1000, null, true, 'fr_FR');  // "1 000,00 $"

// Access data
echo $currency->name;           // "US Dollar"
echo $currency->code;           // "USD"
echo $currency->symbol;         // "$"
echo $currency->symbol_native;  // "$"
```

## Accessors

```php
$product->attachCurrency('NGN');
echo $product->currency_symbol;  // "₦"
```

## Query Scopes

```php
// Products priced in USD
Product::wherePricedIn('USD')->get();
Product::wherePricedIn('eur')->get();  // Case insensitive

// With group
Product::wherePricedIn('USD', 'display')->get();

// By model instance
$usd = Currency::where('code', 'USD')->first();
Product::wherePricedIn($usd)->get();

// Exclude
Product::whereNotPricedIn('USD')->get();
Product::whereNotPricedIn('EUR', 'display')->get();
```

## Multi-Currency

```php
// Display price in USD, settle in EUR
$product->attachCurrency('USD', 'display');
$product->attachCurrency('EUR', 'settlement');

// Show to customer
echo $product->formatMoney(100, 'display');  // "$100.00"

// Calculate settlement
echo $product->formatMoney(85, 'settlement');  // "€85.00"
```

## Related

- [Groups](groups.md)
- [Meta Data](meta-data.md)
