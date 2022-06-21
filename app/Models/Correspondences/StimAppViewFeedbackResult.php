<?php

namespace Vanguard\Models\Correspondences;

use Illuminate\Database\Eloquent\Model;

/**
 * Vanguard\Models\Correspondences\StimAppViewFeedbackResult
 *
 * @property int $id
 * @property int $stim_view_feedback_id
 * @property int|null $by_user_id
 * @property string|null $user_signature
 * @property string|null $received_date
 * @property string|null $notes
 * @property string|null $received_attachments
 * @property string|null $created_at
 * @property string|null $updated_at
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedbackResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedbackResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedbackResult query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedbackResult whereByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedbackResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedbackResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedbackResult whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedbackResult whereReceivedAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedbackResult whereReceivedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedbackResult whereStimViewFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedbackResult whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Vanguard\Models\Correspondences\StimAppViewFeedbackResult whereUserSignature($value)
 */
class StimAppViewFeedbackResult extends Model
{
    public $timestamps = false;

    protected $connection = 'mysql_correspondence';

    protected $table = 'stim_app_view_feedback_results';

    protected $fillable = [
        'stim_view_feedback_id',
        'by_user_id',
        'user_signature',
        'received_date',
        'notes',
        'received_attachments',
    ];
}
