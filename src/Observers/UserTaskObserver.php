<?php

namespace TaskManagement\Observers;

use User\Facade\User;
use Illuminate\Support\Str;
use formbuilder\Entities\Offer;
use formbuilder\Entities\Template as Model;
use formbuilder\Foundation\OfferHistory;
use service\mail\Entities\EmailTranslation;
use Account\Foundation\Collection;
use TaskManagement\Constants\Enums;
use TaskManagement\Entities\UserTask;
use User\Entities\User as UserEntity;
use TaskManagement\Constants\TaskTypes;
use TaskManagement\Events\HandleTaskStatusGroupEvent;
use TaskManagement\Foundation\NotificationCollection;
use formbuilder\Constants\Status as FormBuilderStatus;
use TaskManagement\Foundation\Collection as UserTaskCollection;

class UserTaskObserver
{
    /**
     * @Description saving function trigger for do action on updated.
     * @param UserTask $model
     * @return  void
     */
    public function updated(UserTask $model): void
    {
        if ($model->isDirty('status_uuid')) {
            NotificationCollection::TaskStatusUpdated($model);
            event(new HandleTaskStatusGroupEvent($model, User::getAuthUser()));
        }

        if ($model->isDirty(['reminder_configuration', 'has_reminder', 'due_date'])) {
            UserTaskCollection::HandleUpdateReminder($model);
        }
    }

    /**
     * @Description saving function trigger for do action on updating.
     * @param UserTask $model
     * @return void
     */
    public function updating(UserTask $model): void
    {
        $model->is_editable = false;
        if ($model->type == TaskTypes::TO_DO) {
            $model->is_editable = true;
        }

        if ($model->isDirty('status_uuid') && empty($model->mark_as_completed)) {
            $model->mark_as_completed = 1;
        }
    }

    /**
     * @Description saving function trigger for do action on created.
     * @param UserTask $model
     * @return void
     */
    public function created(UserTask $model): void
    {
        NotificationCollection::TaskCreated($model);
        UserTaskCollection::HandleCreateReminder($model);
    }

    /**
     * @Description saving function trigger for do action on creating.
     * @param UserTask $model
     * @return void
     */
    public function creating(UserTask $model): void
    {
        $model->uuid = Str::uuid()->toString();
        $model->feature = Enums::MANUAL_TASK;
        if (empty($model->account_uuid)) {
            $model->account_uuid = Collection::get_account();
        }
        $model->has_configuration = false;
        $model->configuration = [];
        $model->notes = [];
        $model->attachments = [];

        $model->is_editable = false;
        if ($model->type == TaskTypes::TO_DO) {
            $model->is_editable = true;
        }

        $model->mark_as_completed = 0;
        $model->generateSlug();
        if (!User::isAuth()) {
            $model->creator_uuid = null;
            $model->creator_type = Enums::SYSTEM;
        } elseif (User::getAuthUser() instanceof UserEntity) {
            $model->creator_uuid = User::AuthUuid();
            $model->creator_type = User::isEmployee() ? Enums::EMPLOYEE : Enums::RECRUITER;
        } else {
            $model->creator_uuid = User::AuthUuid();
            $model->creator_type = Enums::CANDIDATE;
        }

        if ($model->type == TaskTypes::OFFER) {
            $additionalData = $model->additional_data;
            $offer = $this->createOffer($model);
            $additionalData["offer_uuid"] = $offer->uuid;
            $model->additional_data = $additionalData;
        }
    }

    /**
     * @Description saving function trigger for do action on deleted.
     * @param UserTask $model
     * @return void
     */
    public function deleted(UserTask $model)
    {
    }

    /**
     * @Description saving function trigger for do action on deleting.
     * @param UserTask $model
     * @return void
     */
    public function deleting(UserTask $model): void
    {
    }

    /**
     * @Description saving function trigger for do action on restored.
     * @param UserTask $model
     * @return void
     */
    public function restored(UserTask $model): void
    {
    }

    /**
     * @Description saving function trigger for do action on restoring.
     * @param UserTask $model
     * @return void
     */
    public function restoring(UserTask $model): void
    {
    }

    private function createOffer(UserTask $model)
    {
        $template = Model::getFirstRow($model->additional_data['template_uuid']);
        $translation = EmailTranslation::where("email_template_id", $model->additional_data['email_template_uuid'])->first();
        $data = [
            'template_uuid' => $template->uuid,
            'candidate_uuid' => $model->relation_uuid,
            'job_uuid' => $model->additional_data['job_uuid'],
            'subject_email' => $translation->subject,
            'body_email' => $translation->body,
            'attachments_email' => []
        ];

        $data['status'] = FormBuilderStatus::draft;
        $data['type'] = Offer::TYPE_MANUAL;
        $data['created_by'] = empty($model->creator_uuid) ? $model->created_by : $model->creator_uuid;
        $data['account_uuid'] = $model->account_uuid;
        $data['company_uuid'] = $template->company_uuid;
        $data['title'] = $template->title;
        $data['description'] = $template->description;
        $data['sections'] = $template->sections;
        $data['type_uuid'] = $template->type_uuid;
        $data['languages'] = $template->languages;
        $data['primary_lang'] = $template->primary_lang;
        $data['secondary_lang'] = $template->secondary_lang;
        $data['layout'] = $template->layout;
        $data['labels_layout'] = $template->labels_layout;
        $data['is_not_shareable'] = $template->is_not_shareable;
        $tmpOffer = OfferHistory::generate(new Offer(), OfferHistory::ACTION_CREATE);
        $data['histories'] = $tmpOffer->histories;
        $create = Offer::create($data);
        //this line for auto approved
        \Approval\Foundation\Collection::AutoWorkflowApprovals($create);

        return $create;
    }
}
