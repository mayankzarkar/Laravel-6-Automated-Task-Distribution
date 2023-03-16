<?php

namespace TaskManagement\Traits;

use App\ats\Rules\AtsJobExists;
use formbuilder\Rules\FormBuilderType;
use formbuilder\Rules\Template;
use service\mail\rules\EmailTemplateExists;
use TaskManagement\Constants\TaskTypes;
use formbuilder\Entities\FormBuilderTypes;

trait AdditionalValidationRule
{
    private static $additional_data_key = "additional_data";

    /**
     * @param int $type
     * @param bool $relationExist
     * @return array
     */
    public function validateAdditionalData(int $type, bool $relationExist = false): array
    {
        $rules = [];
        $additional_data_validations = self::getValidationRules($type, $relationExist);
        foreach ($additional_data_validations as $field => $validation) {
             $rules[static::$additional_data_key . "." . $field] = $validation;
        }

        return $rules;
    }

    /**
     * @return array
     */
    protected static function HandleFormValidation(): array
    {
        return [
            'type_uuid' => ['required', 'string', 'uuid', new FormBuilderType],
            'template_uuid' => ['required', 'string', 'uuid', new Template],
            'job_uuid' => ['required', 'string', 'uuid', new AtsJobExists]
        ];
    }

    /**
     * @return array
     */
    protected static function HandleOfferValidation(): array
    {
        return [
            'type_uuid' => ['required', 'string', 'uuid', new FormBuilderType],
            'template_uuid' => ['required', 'string', 'uuid', new Template],
            'email_template_uuid' => ['nullable', 'string', 'uuid', new EmailTemplateExists],
            'job_uuid' => ['required', 'string', 'uuid', new AtsJobExists]
        ];
    }

    /**
     * @param int $type
     * @return array
     */
    protected static function getValidationRules(int $type, $relationExist): array
    {
        switch ($type) {
            case TaskTypes::FORM:
                $validation = self::HandleFormValidation();
                break;

            case TaskTypes::OFFER:
                $validation = self::HandleOfferValidation();
                break;

            default:
                $validation = [];
                if ($relationExist) {
                    $validation = ['job_uuid' => ['required', 'string', 'uuid', new AtsJobExists]];
                }
                break;
        }

        return $validation;
    }
}
