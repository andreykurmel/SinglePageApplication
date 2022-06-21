<?php

namespace Vanguard\Models\Correspondences;

use Illuminate\Database\Eloquent\Model;
use Vanguard\Models\Table\Table;
use Vanguard\Models\Table\TableField;
use Vanguard\Services\Tablda\HelperService;
use Vanguard\User;

/**
 * Vanguard\Models\Correspondences\StimAppView
 *
 * @property int $id
 * @property int $user_id
 * @property string $v_tab
 * @property string $v_select
 * @property string $source_string
 * @property int $master_row_id
 * @property string $name
 * @property string $hash
 * @property string|null $state
 * @property string $side_top
 * @property string $side_left
 * @property string $side_right
 * @property int $is_active
 * @property int $is_locked
 * @property string|null $lock_pass
 * @mixin \Eloquent
 * @property string|null $visible_tab_ids
 * @property-read \Illuminate\Database\Eloquent\Collection|\Vanguard\Models\Correspondences\StimAppViewFeedback[] $_feedbacks
 * @property-read int|null $_feedbacks_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereLockPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereMasterRowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereSideLeft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereSideRight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereSideTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereSourceString($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereVSelect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereVTab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppView whereVisibleTabIds($value)
 */
class StimAppView extends Model
{
    protected $connection = 'mysql_correspondence';

    protected $table = 'stim_app_views';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'v_tab',
        'v_select',
        'source_string',
        'master_row_id',
        'name',
        'hash',
        'state',
        'is_active',
        'is_locked',
        'lock_pass',
        'side_top',
        'side_left',
        'side_right',
        'visible_tab_ids',
    ];


    public function get_link()
    {
        return (new HelperService())->getUrlWithSubdomain('stim').'/apps/3D?view='.$this->hash;
    }


    public function _feedbacks()
    {
        return $this->hasMany(StimAppViewFeedback::class, 'stim_view_id', 'id');
    }
}
