<?php
return [
    'user_task' => [
        'create' => [
            'title' => 'Task assigned to you!',
            'description' => 'You have a new assigned task :slug.'
        ],
        'task_note_create' => [
            'title' => 'Note added to task!',
            'description' => ':user_name added a note to the task :slug.'
        ],
        'status_change' => [
            'title' => 'Task status updated!',
            'description' => 'Task :slug has been updated to ":status".'
        ],
        'reminder' => [
            'title' => 'Reminder for a pending task!',
            'description' => 'You have a pending task :slug for a :title.'
        ]
    ]
];
