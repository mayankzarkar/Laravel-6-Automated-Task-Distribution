<?php

namespace TaskManagement\Entities;

use Carbon\Carbon;
use App\ats\Entities\JobCandidate;
use App\ats\Entities\Jobs;
use formbuilder\Entities\FormBuilderTypes;
use formbuilder\Entities\Template;
use service\mail\Entities\EmailTemplate;
use TaskManagement\Constants\TaskTypes;
use User\Entities\User;
use Account\Entities\Account;
use Lookup\Entities\TaskStatus;
use Illuminate\Support\Collection;
use User\Facade\User as UserFacade;
use TaskManagement\Constants\Enums;
use Helper\Constants\CanDeleteConstants;
use Jenssegers\Mongodb\Relations\HasMany;
use Helper\Foundation\canDelete\CanDelete;
use Jenssegers\Mongodb\Relations\BelongsTo;
use TaskManagement\Observers\UserTaskObserver;
use Illuminate\Pagination\LengthAwarePaginator;
use candidate\service\Entities\User as Candidate;
use Helper\Foundation\canDelete\CanDeleteInterface;
use TaskManagement\Http\Requests\UserTaskListRequest;
use Account\Foundation\Collection as AccountCollection;

/**
 * @property string $uuid
 * @property string $slug
 * @property int $type
 * @property string $account_uuid
 * @property string $relation_uuid
 * @property int $responsibility_type
 * @property string $responsibility_uuid
 * @property int $creator_type
 * @property string $creator_uuid
 * @property string $status_uuid
 * @property string $title
 * @property string $description
 * @property boolean $enable_notification
 * @property array $tags
 * @property array $attachments
 * @property boolean $has_configuration
 * @property object $configuration
 * @property string $start_date
 * @property string $due_date
 * @property boolean $has_reminder
 * @property object $reminder_configuration
 * @property array $notes
 * @property int $feature
 * @property array $additional_data
 * @property bool $is_editable
 * @property int $mark_as_completed
 *
 * @method static self getFirstRow(string $uuid = null ,string $account_uuid = null ,string $company_uuid = null ,array $extraWhere = [])
 * @method static self[] getListRow(array $uuid ,string $account_uuid = null ,string $company_uuid = null ,array $extraWhere = [])
 *
 **/
class UserTask extends BaseModel implements CanDeleteInterface
{
    use CanDelete;

    protected static function boot()
    {
        parent::boot();
        self::observe(UserTaskObserver::class);
    }

    /**
     * @uses The table name associated with the model.
     * @var string
     */
    protected $table = 'user_task';

    /**
     * @uses The primary key for the model.
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * @uses The primary key type.
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['redirect_url', 'extra_data'];

    /**
     * @uses The columns name for user task table
     * @var array
     */
    protected $fillable = [
        'type',
        'account_uuid',
        'responsibility_type',
        'responsibility_uuid',
        'relation_uuid',
        'status_uuid',
        'title',
        'description',
        'enable_notification',
        'start_date',
        'due_date',
        'has_reminder',
        'reminder_configuration',
        'attachments',
        'tags',
        'notes',
        'additional_data',
        'is_editable',
        'mark_as_completed'
    ];

    /**
     * User task related to the account
     * @return BelongsTo|Null
     */
    final public function account(): ?BelongsTo
    {
        return $this->belongsTo(Account::class,'account_uuid','uuid');
    }

    /**
     * User task related to the reminder
     */
    final public function reminders(): HasMany
    {
        return $this->hasMany(UserTaskReminder::class,'user_task_uuid', 'uuid');
    }

    /**
     * User task related to the status
     * @return BelongsTo|Null
     */
    final public function taskStatus(): ?BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'status_uuid', 'uuid');
    }

    /**
     * User task related to the reminder
     */
    final public function pendingReminders(): HasMany
    {
        return $this->hasMany(UserTaskReminder::class,'user_task_uuid', 'uuid')->pending();
    }

    /**
     * User task related to the responsibility
     * @return BelongsTo|Null
     */
    final public function responsibility(): ?BelongsTo
    {
        switch ($this->responsibility_type) {
            case Enums::RECRUITER:
            case Enums::EMPLOYEE:
                $relativeClass = User::class;
                break;

            default:
                $relativeClass = Candidate::class;
                break;
        }

        return $this->belongsTo($relativeClass,'responsibility_uuid','uuid');
    }

    /**
     * User task related to the creator
     * @return BelongsTo|Null
     */
    final public function creator(): ?BelongsTo
    {
        switch ($this->creator_type) {
            case Enums::SYSTEM:
                return null;

            case Enums::RECRUITER:
            case Enums::EMPLOYEE:
                $relativeClass = User::class;
                break;

            default:
                $relativeClass = Candidate::class;
                break;
        }

        return $this->belongsTo($relativeClass,'creator_uuid','uuid');
    }

    /**
     * User task related to the candidate
     * @return BelongsTo|Null
     */
    final public function relation(): ?BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'relation_uuid', 'uuid');
    }

    /**
     * Get all user tasks
     * @param string $account_uuid
     * @param string $user_uuid
     * @param UserTaskListRequest $request
     * @param boolean $is_paginate
     * @return Collection|LengthAwarePaginator
     */
    final public static function getUserTasks(string $account_uuid, string $user_uuid, UserTaskListRequest $request, bool $is_paginate = true)
    {
        $query = self::where('account_uuid', $account_uuid);
        if (!UserFacade::AuthIsSuperUser()) {
            // Filter by creator / assignee / relation
            $query->where(function ($qb) use ($user_uuid) {
                return $qb->where('creator_uuid', $user_uuid)
                    ->orWhere('responsibility_uuid', $user_uuid)
                    ->orWhere('relation_uuid', $user_uuid);
            });
        }

        if ($query_string = $request->get('query')) {
            $query->where('title', 'LIKE', "%{$query_string}%");
        }

        if ($feature = $request->feature) {
            $query->where('feature', (int)$feature);
        }

        if ($responsibility_type = $request->get('responsibility_type')) {
            $query->where('responsibility_type', (int)$responsibility_type);
        }

        if ($responsibility_uuid = $request->get('responsibility_uuid')) {
            $query->where('responsibility_uuid', $responsibility_uuid);
        }

        if ($relation_uuid = $request->get('relation_uuid')) {
            $query->where('relation_uuid', $relation_uuid);
        }

        if (($date_filters = $request->get('date_filter')) && ($start_date = $request->get('date_range_start')) && ($end_date = $request->get('date_range_end'))) {
            $start_date = Carbon::parse($start_date)->format("Y-m-d 00:00:00");
            $end_date = Carbon::parse($end_date)->format("Y-m-d 23:59:59");

            foreach ($date_filters as $filter) {
                $between = [$start_date, $end_date];
                if (in_array($filter, [Enums::FILTER_CREATED_AT, Enums::FILTER_UPDATED_AT])) {
                    $between = [Carbon::parse($start_date), Carbon::parse($end_date)];
                }

                $query->whereBetween(Enums::DATE_FILTERS[$filter], $between);
            }
        }

        if ($request->order == BaseModel::DESC) {
            $query->orderByDesc($request->order_by);
        } else {
            $query->orderBy($request->order_by);
        }

        if ($is_paginate) {
            return $query->paginate($request->limit)->onEachSide($request->page);
        }

        return $query->get();
    }

    /**
     * @param $query
     * @param string $account_uuid
     * @return mixed
     */
    public function scopeForAccount($query, string $account_uuid)
    {
        return $query->where('account_uuid', $account_uuid);
    }

    /**
     * @param string $slug
     * @return UserTask|null
     */
    public static function findBySlug(string $slug): ?UserTask
    {
        return self::forAccount(AccountCollection::get_account())->where('slug', $slug)->first();
    }

    /**
     * @return self
     */
    final public function generateSlug(): self
    {
        $account = $this->account;
        $last_slug = self::forAccount($account->uuid)->latest()->first();
        if (!empty($last_slug) && !empty($last_slug->slug)) {
            $slug = explode("-", $last_slug->slug);
            $task_id = end($slug);
            $this->slug = str_replace(' ', '-', strtoupper($account->name['en']) ?: 'task').'-'.sprintf("%03d", ($task_id + 1));
        } else {
            $this->slug = str_replace(' ', '-', strtoupper($account->name['en'] ?: 'task')).'-'.sprintf("%03d", 1);
        }

        return $this;
    }

    /**
     * @return array
     */
    final public function relationDelete(): array
    {
        return [];
    }

    /**
     * @return array
     */
    final public function canDelete(): array
    {
        return [
            "status_uuid" => [
                "class"=> TaskStatus::class,
                "type" => CanDeleteConstants::STRING,
                "primary_key" => "uuid"
            ]
        ];
    }

    public static function GetAdditionalData(int $type, array $additionalData): array
    {
        if (empty($additionalData)) {
            return [];
        }

        switch ($type) {
            case TaskTypes::OFFER:
                $additionalData = self::PrepareBasicAdditionalData($additionalData);
                $emailTemplate = EmailTemplate::findByPk($additionalData["email_template_uuid"]);
                if (!empty($template)) {
                    $additionalData["email_template"] = [
                        'uuid' => $emailTemplate->uuid,
                        'name' => $emailTemplate->title
                    ];

                    unset($additionalData["email_template_uuid"]);
                }
                break;

            case TaskTypes::FORM:
                $additionalData = self::PrepareBasicAdditionalData($additionalData);
                break;

            default:
                if (!empty($additionalData["job_uuid"])) {
                    $job = Jobs::findByPk($additionalData["job_uuid"]);
                    if (!empty($job)) {
                        $additionalData["job"] = [
                            'uuid' => $job->uuid,
                            'name' => $job->title
                        ];

                        unset($additionalData["job_uuid"]);
                    }
                }
                break;
        }

        return $additionalData;
    }

    /**
     * @param array $additionalData
     * @return array
     */
    private static function PrepareBasicAdditionalData(array $additionalData): array
    {
        $type = FormBuilderTypes::findByPk($additionalData["type_uuid"]);
        if (!empty($type)) {
            $additionalData["type"] = [
                'uuid' => $type->uuid,
                'name' => $type->name
            ];

            unset($additionalData["type_uuid"]);
        }

        $template = Template::findByPk($additionalData["template_uuid"]);
        if (!empty($template)) {
            $additionalData["template"] = [
                'uuid' => $template->uuid,
                'name' => $template->title
            ];

            unset($additionalData["template_uuid"]);
        }

        if (!empty($additionalData["job_uuid"])) {
            $job = Jobs::findByPk($additionalData["job_uuid"]);
            if (!empty($job)) {
                $additionalData["job"] = [
                    'uuid' => $job->uuid,
                    'name' => $job->title
                ];

                unset($additionalData["job_uuid"]);
            }
        }

        return $additionalData;
    }

    /**
     * Determine additional data
     */
    public function getRedirectUrlAttribute(): ?string
    {
        switch ($this->type) {
            case TaskTypes::OFFER:
                return $this->generateRedirectURLForOffer();

            case TaskTypes::FORM:
                return $this->generateRedirectURLForForm();

            default:
                return null;
        }
    }

    private function generateRedirectURLForOffer(): ?string
    {
        $params = [
            "template_uuid" => null,
            "template_type_uuid" => null,
            "job_uuid" => null,
            "status" => 1,
            "editorRole" => "sender",
            "form_uuid" => null,
            "pipeline_uuid" => null
        ];

        $job = Jobs::findByPk($this->additional_data["job_uuid"]);
        $jobCandidate = JobCandidate::searchByCandidateAndJob($job->uuid, $this->relation_uuid);
        if (empty($jobCandidate)) {
            return null;
        }
        $params["template_type_uuid"] = $this->additional_data["type_uuid"];
        $params["template_uuid"] = $this->additional_data["template_uuid"];
        $params["form_uuid"] = $this->additional_data["offer_uuid"];
        $params["job_uuid"] = $job->uuid;
        $params["pipeline_uuid"] = $jobCandidate->stage->ats_job_pipelines_uuid;

        return url()->to(
            config('helper.base_url_frontend') ."form-builder/info?". http_build_query($params)
        );
    }

    private function generateRedirectURLForForm(): ?string
    {
        $params = [
            "template_uuid" => null,
            "code" => null,
            "job_uuid" => null,
            "candidate_uuid" => null,
            "editor_role" => "sender",
            "assign_uuid" => null,
            "pipeline_uuid" => null,
            "stage_uuid" => null
        ];

        $job = Jobs::findByPk($this->additional_data["job_uuid"]);
        $type = FormBuilderTypes::findByPk($this->additional_data["type_uuid"]);
        $jobCandidate = JobCandidate::searchByCandidateAndJob($job->uuid, $this->relation_uuid);
        if (empty($jobCandidate)) {
            return null;
        }

        $params["template_uuid"] = $this->additional_data["template_uuid"];
        $params["code"] = $type->code;
        $params["job_uuid"] = $job->uuid;
        $params["candidate_uuid"] = $this->relation_uuid;
        $params["assign_uuid"] = $jobCandidate->uuid;
        $params["pipeline_uuid"] = $jobCandidate->stage->ats_job_pipelines_uuid;
        $params["stage_uuid"] = $jobCandidate->stage_uuid;

        return url()->to(
            config('helper.base_url_frontend') ."forms?". http_build_query($params)
        );
    }

    /**
     * Determine extra data
     */
    public function getExtraDataAttribute(): array
    {
        if (empty($this->relation_uuid) || empty($this->additional_data['job_uuid'])) {
            return [];
        }

        $jobCandidate = JobCandidate::searchByCandidateAndJob($this->additional_data['job_uuid'], $this->relation_uuid);
        if (empty($jobCandidate)) {
            return [];
        }

        $target = $jobCandidate->job->JobRequisition->positionTitle ?? null;
        $position_title = $target ? $target->getName() : $jobCandidate->job_title();
        return [
            "job_uuid" => $jobCandidate->job_uuid,
            "applicant_number" => $jobCandidate->applicant_number,
            "pipeline_uuid" => $jobCandidate->stage->ats_job_pipelines_uuid,
            "branch_uuid" => $jobCandidate->company_uuid,
            "reference_number" => $jobCandidate->user->reference_number,
            "position_title" => $position_title
        ];
    }
}
