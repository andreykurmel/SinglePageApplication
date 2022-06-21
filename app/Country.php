<?php

namespace Vanguard;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Country
 *
 * @property int $id
 * @property string|null $capital
 * @property string|null $citizenship
 * @property string $country_code
 * @property string|null $currency
 * @property string|null $currency_code
 * @property string|null $currency_sub_unit
 * @property string|null $currency_symbol
 * @property string|null $full_name
 * @property string $iso_3166_2
 * @property string $iso_3166_3
 * @property string $name
 * @property string $region_code
 * @property string $sub_region_code
 * @property int $eea
 * @property string|null $calling_code
 * @property string|null $flag
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereCallingCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereCapital($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereCitizenship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereCurrencySubUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereCurrencySymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereEea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereIso31662($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereIso31663($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereRegionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Country whereSubRegionCode($value)
 * @mixin \Eloquent
 */
class Country extends Model
{
    protected $table = 'countries';

    public $timestamps = false;
}
