<?php
namespace TaskManagement\Foundation;

interface TaskManagementInterface
{
    /**
     * Used for getting properties of entity
     * @return array
     */
    public static function GetTMProperties(): array;

    /**
     * Used for getting validations of the properties
     * @return array
     */
    public static function GetTMValidations(): array;

    /**
     * Used for getting values of the properties
     * @return array
     */
    public function getTMValues(): array;
}
