<?php

namespace TaskManagement\Traits;

use User\Rules\UserUuidExist;
use Illuminate\Validation\Rule;
use TaskManagement\Constants\Enums;
use TaskManagement\Constants\Actions;
use TaskManagement\Constants\TaskTypes;
use Pipeline\Service\Rules\V2\StageExists;
use service\mail\rules\EmailTemplateExists;
use candidate\service\Rules\CandidateExists;
use Lookup\Rules\AccountLevel\TaskTypeExists;
use Lookup\Rules\AccountLevel\TaskStatusExists;

trait ActionDataRule
{
    private static $action_data_key = "action_data";

    /**
     * @param int|null $action
     * @return array
     */
    public function validateActionData(?int $action): array
    {
        if (is_null($action)) {
            return [];
        }

        $validations = $this->getActionValidations($action);
        $rules = [];
        foreach ($validations as $field => $validation) {
            $rules[static::$action_data_key . "." . $field] = $validation;
        }

        return $rules;
    }

    /**
     * @param int $action
     * @return array
     */
    private function getActionValidations(int $action): array
    {
        $actions = [
            Actions::ACTION_SEND_EMAIL => [
                "template_uuid" => [
                    "bail",
                    "required_without:".static::$action_data_key.".subject",
                    "nullable",
                    "uuid",
                    new EmailTemplateExists
                ],
                "subject" => ["required_without:".static::$action_data_key.".template_uuid", "nullable", "string"],
                "body" => ["required_without:".static::$action_data_key.".template_uuid", "nullable", "string"],
                "receiver_type" => [
                    "required",
                    "integer",
                    Rule::in(array_keys(Actions::SEND_EMAIL_TYPES))
                ],
                "to_email" => [$this->isOtherSendEmailType() ? "required" : "nullable", "email"],
                "to_name" => [$this->isOtherSendEmailType() ? "required" : "nullable", "string"]
            ],
            Actions::ACTION_MOVE_CANDIDATE_TO => [
                "stage_uuid" => ["required", "string", "uuid", new StageExists]
            ],
            Actions::ACTION_CREATE_TASK => [
                "title" => ["required", "string"],
                "description" => ["nullable", "string"],
                "status_uuid" => ["required", "string", "uuid", new TaskStatusExists],
                "type" => ["required", "integer", Rule::in(array_keys(TaskTypes::TYPES))],
                "enable_notification" => ["required", "boolean"],
                "responsibility_type" => ["required", "integer", Rule::in(array_keys(Enums::USER_RESPONSIBILITY))],
                "responsibility_uuid" => ["nullable", Rule::requiredIf(!empty($this->action_data['responsibility_type']) && $this->action_data['responsibility_type'] !== Enums::REQUESTER), "string", "uuid", $this->checkResponsibility()],
                "duration" => ["nullable", "integer"],
                "has_reminder" => ["boolean"],
                "reminder_configuration" => ["nullable", "array", Rule::requiredIf(!empty($this->action_data['has_reminder']))],
                "reminder_configuration.type" => [Rule::requiredIf(!empty($this->action_data['reminder_configuration'])), "array"],
                "reminder_configuration.type.*" => [Rule::requiredIf(!empty($this->action_data['reminder_configuration'])), "integer", Rule::in(array_keys(Enums::REMINDER_TYPES))],
                "reminder_configuration.frequency" => [Rule::requiredIf(!empty($this->action_data['reminder_configuration'])), "integer", Rule::in(array_keys(Enums::REMINDER_FREQUENCIES))],
                "reminder_configuration.is_recursive" => [Rule::requiredIf(!empty($this->action_data['reminder_configuration'])), "boolean"],
                "tags" => ["nullable", "array"]
            ] + $this->handleAdditionalData()
        ];

        return $actions[$action];
    }

    public function isOtherSendEmailType(): bool
    {
        $actionData = $this->get(static::$action_data_key, []);
        if (!empty($actionData['receiver_type']) && $actionData['receiver_type'] == Actions::SEND_EMAIL_TYPE_OTHER) {
            return true;
        }

        return false;
    }

    /**
     * Check the logged-in user
     * @return UserUuidExist
     */
    private function checkResponsibility(): UserUuidExist
    {
        $actionData = $this->get(static::$action_data_key, []);
        if (!empty($actionData['responsibility_type'])) {
            switch ($actionData['responsibility_type']) {
                case Enums::RECRUITER:
                    return new UserUuidExist;

                default:
                    return new UserUuidExist(false);
            }
        }

        return new UserUuidExist;
    }

    /**
     * @return array
     */
    private function handleAdditionalData(): array
    {
        $data = $this->get(static::$action_data_key);
        $type = $data['type'] ?? TaskTypes::TO_DO;
        $rules = [
            "additional_data" => [$type != TaskTypes::TO_DO ? 'required' : 'nullable', 'array']
        ];

        if ($type != TaskTypes::TO_DO) {
            $validations = $this->validateAdditionalData($type);
            if (array_key_exists("additional_data.job_uuid", $validations)) {
                unset($validations["additional_data.job_uuid"]);
            }

            $rules = $rules + $validations;
        }

        return $rules;
    }
}
