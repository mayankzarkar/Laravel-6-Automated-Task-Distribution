<?php

namespace TaskManagement\Http\Requests;

use TaskManagement\Traits\AdditionalValidationRule;
use User\Rules\UserUuidExist;
use Illuminate\Validation\Rule;
use TaskManagement\Constants\Enums;
use Illuminate\Support\Facades\Date;
use Helper\Http\Requests\BaseRequest;
use TaskManagement\Constants\TaskTypes;
use TaskManagement\Rules\UserTaskExists;
use candidate\service\Rules\CandidateExists;
use Lookup\Rules\AccountLevel\TaskStatusExists;

/**
 * @property string $uuid
 * @property string $type_uuid
 * @property int $responsibility_type
 * @property string $responsibility_uuid
 * @property string $relation_uuid
 * @property string $title
 * @property string $description
 * @property bool $enable_notification
 * @property Date $start_date
 * @property Date $due_date
 * @property bool $has_reminder
 * @property array $reminder_configuration
 * @property array $tags
 **/
class UserTaskRequest extends BaseRequest
{
    use AdditionalValidationRule;

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'type' => ['required', 'integer', Rule::in(array_keys(TaskTypes::TYPES))],
            'responsibility_type' => ['required', 'integer', Rule::in(array_keys(Enums::RESPONSIBILITY))],
            'responsibility_uuid' => ['required', 'string', 'uuid', $this->checkResponsibility()],
            'relation_uuid' => [$this->type != TaskTypes::TO_DO ? 'required' : 'nullable', 'string', 'uuid', new CandidateExists],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'status_uuid' => ['required', 'string', 'uuid', new TaskStatusExists],
            'enable_notification' => ['required', 'boolean'],
            'start_date' => ['nullable', 'date_format:Y-m-d H:i:s', 'after_or_equal:today'],
            'due_date' => ['nullable', 'date_format:Y-m-d H:i:s', 'after_or_equal:start_date'],
            'has_reminder' => ['boolean'],
            'reminder_configuration' => ['nullable', 'array', Rule::requiredIf($this->has_reminder)],
            'reminder_configuration.type' => [Rule::requiredIf($this->reminder_configuration), 'array'],
            'reminder_configuration.type.*' => [Rule::requiredIf($this->reminder_configuration), 'integer', Rule::in(array_keys(Enums::REMINDER_TYPES))],
            'reminder_configuration.frequency' => [Rule::requiredIf($this->reminder_configuration), 'integer', Rule::in(array_keys(Enums::REMINDER_FREQUENCIES))],
            'reminder_configuration.is_recursive' => [Rule::requiredIf($this->reminder_configuration), 'boolean'],
            'tags' => ['nullable', 'array'],
            'additional_data' => [$this->type != TaskTypes::TO_DO ? 'required' : 'nullable', 'array']
        ];

        if ($this->get('type')) {
            // validating additional data
            $rules = array_merge($rules, $this->validateAdditionalData($this->get('type'), !empty($this->get('relation_uuid'))));
        }

        if ($this->getMethod() == "PUT") {
            $rules['uuid'] = ['required', 'string', 'uuid', new UserTaskExists];
        }

        return $rules;
    }

    /**
     * Check the logged-in user
     * @return CandidateExists|UserUuidExist
     */
    private function checkResponsibility()
    {
        switch ($this->responsibility_type) {
            case Enums::RECRUITER:
                return new UserUuidExist;

            case Enums::EMPLOYEE:
                return new UserUuidExist(false);

            default:
                return new CandidateExists;
        }
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'reminder_configuration' => $this->get('has_reminder') ? $this->get('reminder_configuration') : [],
            'additional_data' => !empty($this->get('additional_data')) ? $this->get('additional_data') : []
        ]);
    }
}
