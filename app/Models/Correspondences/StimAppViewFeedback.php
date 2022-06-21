<?php

namespace Vanguard\Models\Correspondences;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Correspondences\StimAppViewFeedback
 *
 * @property int $id
 * @property int $stim_view_id
 * @property string|null $purpose
 * @property string|null $email_to
 * @property string|null $email_cc
 * @property string|null $email_bcc
 * @property string|null $email_subject
 * @property string|null $email_body
 * @property string|null $send_date
 * @property string|null $request_pass
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read StimAppViewFeedbackResult[] $_results
 * @property-read StimAppView $_view
 * @mixin \Eloquent
 * @property string $part_uuid
 * @property-read int|null $_results_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback whereEmailBcc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback whereEmailBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback whereEmailCc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback whereEmailSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback whereEmailTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback wherePartUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback whereRequestPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback whereSendDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback whereStimViewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedback whereUpdatedAt($value)
 */
class StimAppViewFeedback extends Model
{
    public $timestamps = false;

    protected $connection = 'mysql_correspondence';

    protected $table = 'stim_app_view_feedbacks';

    protected $fillable = [
        'stim_view_id',
        'part_uuid',
        'purpose',
        'email_to',
        'email_cc',
        'email_bcc',
        'email_subject',
        'email_body',
        'send_date',
        'request_pass',
    ];


    public function link_to_feedback()
    {
        return $this->_view->get_link() . '&f=' . $this->part_uuid;
    }


    public function _results()
    {
        return $this->hasMany(StimAppViewFeedbackResult::class, 'stim_view_feedback_id', 'id');
    }

    public function _view()
    {
        return $this->belongsTo(StimAppView::class, 'stim_view_id', 'id');
    }
}
