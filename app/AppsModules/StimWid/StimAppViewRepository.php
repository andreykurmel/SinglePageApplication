<?php

namespace Vanguard\AppsModules\StimWid;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Vanguard\Mail\EmailWithSettings;
use Vanguard\Models\Correspondences\StimAppView;
use Vanguard\Models\Correspondences\StimAppViewFeedback;
use Vanguard\Models\Correspondences\StimAppViewFeedbackResult;
use Vanguard\Repositories\Tablda\TableData\TableDataRowsRepository;
use Vanguard\Repositories\Tablda\TableRepository;
use Vanguard\Repositories\Tablda\UserRepository;
use Vanguard\Services\Tablda\HelperService;

class StimAppViewRepository
{
    protected $email_body_template = '
Dear sir,

Please click the link below to review:
$view_link

Best,
Your name
    ';
    protected $email_subject_template = 'Feedback request for View: $view_name.';

    protected $service;
    protected $settRepo;

    /**
     * TableViewRepository constructor.
     *
     */
    public function __construct()
    {
        $this->service = new HelperService();
        $this->settRepo = new StimSettingsRepository();
    }

    /**
     * @return StimAppView[]
     */
    public function loadUsersViews()
    {
        $views = StimAppView::where('user_id', '=', auth()->id())->get();
        $this->loadRelations($views);
        foreach ($views as $v) {
            $v->__tabs_tree = $this->settRepo->tabsTree($v->v_tab, $v->v_select);
        }
        return $views;
    }

    /**
     * @param Collection|StimAppView $views
     */
    public function loadRelations(Collection $views): void
    {
        $views->load([
            '_feedbacks' => function ($q) {
                $q->with('_results');
            },
        ]);

        $res_table = (new TableRepository())->getTableByDB('stim_app_view_feedback_results');
        $repo = new TableDataRowsRepository();
        foreach ($views as $view) {
            foreach ($view->_feedbacks as $feedback) {
                $repo->attachSpecialFields($feedback->_results, $res_table, $res_table->user_id, ['files']);
            }
        }
    }

    /**
     * @param $hash
     * @return mixed
     */
    public function getByHash($hash)
    {
        return StimAppView::where('hash', '=', $hash)
            ->where('is_active', '=', 1)
            ->first();
    }

    /**
     * @param array $data
     * @return Model|StimAppView
     * @throws Exception
     */
    public function insertAppView(array $data)
    {
        $data['name'] = preg_replace('/[^\w\d_\s]/i', '', $data['name']);
        $data['hash'] = Uuid::uuid4();
        $data['user_id'] = auth()->id();
        $data['side_top'] = $data['side_top'] ?? 'na';
        $data['side_left'] = $data['side_left'] ?? 'na';
        $data['side_right'] = $data['side_right'] ?? 'na';
        $data['is_active'] = 1;
        $data['visible_tab_ids'] = null;
        $app_view = StimAppView::create($this->service->delSystemFields($data));
        $app_view->__tabs_tree = $this->settRepo->tabsTree($app_view->v_tab, $app_view->v_select);
        $app_view->load([
            '_feedbacks' => function ($q) {
                $q->with('_results');
            },
        ]);
        return $app_view;
    }

    /**
     * @param $view_id
     * @param array $data
     * @return bool
     */
    public function updateAppView($view_id, array $data)
    {
        if (!empty($data['name'])) {
            $data['name'] = preg_replace('/[^\w\d_\s]/i', '', $data['name']);
        }
        return StimAppView::where('id', '=', $view_id)->update($this->service->delSystemFields($data));
    }

    /**
     * @param $table_view_id
     * @return bool|null
     * @throws Exception
     */
    public function deleteAppView($table_view_id)
    {
        return StimAppView::where('id', '=', $table_view_id)->delete();
    }

    /**
     * @param StimAppView $view
     * @param array $data
     * @return StimAppView
     */
    public function insertAppViewFeedback(StimAppView $view, array $data): StimAppView
    {
        $data['stim_view_id'] = $view->id;
        $data['email_body'] = trim($this->email_body_template);
        $data['email_subject'] = trim($this->email_subject_template);
        $data['part_uuid'] = substr(Str::uuid(), 0, 5);

        $feedback = StimAppViewFeedback::create($data);
        $feedback['email_body'] = $this->parseBody($feedback, true);//replace $view_link to value

        return $this->updateAppViewFeedback($view, $feedback->toArray());
    }

    /**
     * @param StimAppViewFeedback $feedback
     * @param bool $as_string
     * @return string
     */
    protected function parseBody(StimAppViewFeedback $feedback, bool $as_string = false): string
    {
        $body = $as_string ? $feedback->email_body : nl2br($feedback->email_body);
        return $this->replaceVariables($feedback, $body);
    }

    /**
     * @param StimAppViewFeedback $feedback
     * @param string $data
     * @return string
     */
    protected function replaceVariables(StimAppViewFeedback $feedback, string $data): string
    {
        $user = (new UserRepository())->getById($feedback->_view->user_id);
        $link = '<a href="' . $feedback->link_to_feedback() . '">' . $feedback->link_to_feedback() . '</a>';

        $data = preg_replace('/\$view_link/i', $link, $data);
        $data = preg_replace('/\$view_name/i', $feedback->_view->name, $data);
        $data = preg_replace('/\$user/i', $user->full_name(), $data);
        $data = preg_replace('/\$company/i', config('app.name'), $data);
        $data = preg_replace('/\$purpose/i', $feedback->purpose, $data);

        return $data;
    }

    /**
     * @param StimAppView $view
     * @param array $data
     * @return StimAppView
     */
    public function updateAppViewFeedback(StimAppView $view, array $data): StimAppView
    {
        $data['stim_view_id'] = $view->id;
        StimAppViewFeedback::where('stim_view_id', '=', $view->id)
            ->where('id', '=', $data['id'])
            ->update($this->service->delSystemFields($data));
        return $this->loadAppViewWithRelations($view->id);
    }

    /**
     * @param int $id
     * @return StimAppView
     */
    public function loadAppViewWithRelations(int $id): StimAppView
    {
        $views = $this->getAppView([$id]);
        $this->loadRelations($views);
        return $views->first();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getAppView($id)
    {
        return is_array($id)
            ? StimAppView::whereIn('id', $id)->get()
            : StimAppView::where('id', '=', $id)->first();
    }

    /**
     * @param StimAppViewFeedback $feedback
     * @return Collection
     */
    public function loadResults(StimAppViewFeedback $feedback): Collection
    {
        $results = $feedback->_results()
            ->get()
            ->sortByDesc('received_date');

        $res_table = (new TableRepository())->getTableByDB('stim_app_view_feedback_results');
        $results = (new TableDataRowsRepository())->attachSpecialFields($results, $res_table, $res_table->user_id, ['files']);

        return $results->values();
    }

    /**
     * @param StimAppView $view
     * @return Collection
     */
    public function loadRequests(StimAppView $view): Collection
    {
        return $view->_feedbacks()->get();
    }

    /**
     * @param StimAppViewFeedback $feedback
     * @return StimAppView
     */
    public function setFeedbackSent(StimAppViewFeedback $feedback): StimAppView
    {
        StimAppViewFeedback::where('stim_view_id', '=', $feedback->stim_view_id)
            ->where('id', '=', $feedback->id)
            ->update([
                'send_date' => now(),
            ]);
        return $this->loadAppViewWithRelations($feedback->stim_view_id);
    }

    /**
     * @param StimAppView $view
     * @param int $feedback_id
     * @return StimAppView
     * @throws Exception
     */
    public function deleteAppViewFeedback(StimAppView $view, int $feedback_id): StimAppView
    {
        StimAppViewFeedback::where('stim_view_id', '=', $view->id)
            ->where('id', '=', $feedback_id)
            ->delete();
        return $this->loadAppViewWithRelations($view->id);
    }

    /**
     * @param StimAppViewFeedback $feedback
     * @param array $data
     * @return StimAppViewFeedbackResult
     */
    public function insertAppViewFeedbackResult(StimAppViewFeedback $feedback, array $data): StimAppViewFeedbackResult
    {
        $this->updateAppViewState($feedback->stim_view_id);

        $data['stim_view_feedback_id'] = $feedback->id;
        $data['received_date'] = now()->toDateTimeString();
        $data['by_user_id'] = auth()->id();

        return StimAppViewFeedbackResult::create($data);
    }

    /**
     * @param $view_id
     * @return bool
     */
    public function updateAppViewState($view_id)
    {
        return StimAppView::where('id', '=', $view_id)
            ->update(['state' => Str::uuid()]);
    }

    /**
     * @param string $field
     * @param $search
     * @return StimAppViewFeedback
     */
    public function getFeedbackBy(string $field, $search): StimAppViewFeedback
    {
        return StimAppViewFeedback::where($field, '=', $search)
            ->first();
    }

    /**
     * @param StimAppViewFeedback $feedback
     * @param array $data
     * @return StimAppViewFeedback
     */
    public function updateAppViewFeedbackResult(StimAppViewFeedback $feedback, array $data): StimAppViewFeedback
    {
        $this->updateAppViewState($feedback->stim_view_id);

        $data['stim_view_feedback_id'] = $feedback->id;
        StimAppViewFeedbackResult::where('stim_view_feedback_id', '=', $feedback->id)
            ->where('id', '=', $data['id'])
            ->update($this->service->delSystemFields($data));
        return $this->getFeedback($feedback->id);
    }

    /**
     * @param int $feedback_id
     * @return StimAppViewFeedback
     */
    public function getFeedback(int $feedback_id): StimAppViewFeedback
    {
        return StimAppViewFeedback::where('id', '=', $feedback_id)
            ->with('_results')
            ->first();
    }

    /**
     * @param StimAppViewFeedback $feedback
     * @param int $result_id
     * @return StimAppViewFeedback
     * @throws Exception
     */
    public function deleteAppViewFeedbackResult(StimAppViewFeedback $feedback, int $result_id): StimAppViewFeedback
    {
        $this->updateAppViewState($feedback->stim_view_id);

        StimAppViewFeedbackResult::where('stim_view_feedback_id', '=', $feedback->id)
            ->where('id', '=', $result_id)
            ->delete();
        return $this->getFeedback($feedback->id);
    }

    /**
     * @param StimAppViewFeedback $feedback
     * @return string
     */
    public function sendFeedbackEmail(StimAppViewFeedback $feedback): string
    {
        $recipients = [];
        $recipients['to'] = $this->service->parseRecipients($feedback->email_to ?: '', true);
        $recipients['cc'] = $this->service->parseRecipients($feedback->email_cc ?: '', true);
        $recipients['bcc'] = $this->service->parseRecipients($feedback->email_bcc ?: '', true);

        $params = [
            'from.name' => config('app.name') . ' ANA',
            'from.account' => 'noreply',
            'subject' => $this->parseSubject($feedback),
            'to.address' => $recipients['to'],
        ];

        foreach ($recipients['to'] as $to) {
            $data = [
                'content_body' => $this->parseBody($feedback),
            ];

            $mailer = new EmailWithSettings('stim_view_request_feedback', $to, $recipients['cc'] ?? [], $recipients['bcc'] ?? []);
            $mailer->send($params, $data);
        }

        return 'Email is sent';
    }

    /**
     * @param StimAppViewFeedback $feedback
     * @return string
     */
    protected function parseSubject(StimAppViewFeedback $feedback): string
    {
        $subject = nl2br($feedback->email_subject);
        return $this->replaceVariables($feedback, $subject);
    }
}