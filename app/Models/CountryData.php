<?php

namespace Vanguard\Models;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\User;

/**
 * Vanguard\Models\CountryData
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
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereCallingCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereCapital($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereCitizenship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereCurrencySubUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereCurrencySymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereEea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereIso31662($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereIso31663($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereRegionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\CountryData whereSubRegionCode($value)
 * @mixin \Eloquent
 */
class CountryData extends Model
{
    protected $table = 'countries';

    public $timestamps = false;
}
