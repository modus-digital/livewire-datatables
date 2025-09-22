<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Color
    |--------------------------------------------------------------------------
    |
    | Sets the base Tailwind color used by the datatable components (headers,
    | buttons, highlights). Use one of the supported Tailwind color names.
    |
    | Supported options: "red", "orange", "yellow", "green", "blue",
    |                    "indigo", "purple", "pink", "teal", "cyan",
    |                    "lime", "emerald", "sky"
    |
    */

    'color' => 'indigo',

    /*
    |--------------------------------------------------------------------------
    | Row Selection Checkboxes
    |--------------------------------------------------------------------------
    |
    | When enabled, a leading checkbox column is rendered to allow users to
    | select one or more rows for bulk actions.
    |
    */

    'checkboxes' => false,

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    |
    | Configure how filters are displayed and behave in the datatable UI.
    |
    */

    'filters' => [

        /*
        |--------------------------------------------------------------------------
        | Labels
        |--------------------------------------------------------------------------
        |
        | Controls whether labels are shown next to filter inputs. When enabled,
        | filter fields render with their corresponding label.
        |
        */

        'labels' => false,

        /*
        |--------------------------------------------------------------------------
        | Style
        |--------------------------------------------------------------------------
        |
        | Selects the UI style used to present filters to the user.
        |
        | Supported options: "simple", "popup"
        |
        */

        'style' => 'simple',

        /*
        |--------------------------------------------------------------------------
        | Active Filters Ribbon
        |--------------------------------------------------------------------------
        |
        | Controls whether a ribbon with the currently active filters is displayed
        | above the table. When enabled, users can quickly review and clear
        | applied filters.
        |
        */

        'ribbon' => true,
    ],
];
