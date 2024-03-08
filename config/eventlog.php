<?php

// config for Dtdi/Eventlog

return [

    /*
     * This model will be used as base event.
     * and extend Illuminate\Database\Eloquent\Model.
     */
    'event_model' => null,

    'event_id' => 'id',
    'timestamp' => 'created_at',
    'event_name' => 'action_type',

];
