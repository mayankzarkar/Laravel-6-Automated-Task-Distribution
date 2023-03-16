<?php
namespace TaskManagement\Foundation;

interface TaskManagementFilterInterface
{
    /**
     * Used for getting the filter options
     * @return array
     */
    public static function GetFilterOptions(): array;
}
