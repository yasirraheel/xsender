<?php

use App\Enums\SettingKey;

/**
 * This file configures demo mode functionality, controlling restricted actions and periodic database resets.
 *
 * Structure:
 * - `enabled`: Boolean (via DEMO_MODE env) to toggle demo mode globally.
 * - `messages`: Contains the `global` message, used as the fallback for all demo mode notifications.
 * - `database_reset_unit`: Time unit for database resets ('second', 'minute', 'hour', 'day', 'month', 'year').
 * - `database_reset_duration`: Integer duration for reset intervals (e.g., 4 for 4 hours).
 * - `feature`: Array of restricted features (e.g., 'settings', 'admin_profile'), each with:
 *   - `enabled`: Boolean to toggle feature restrictions.
 *   - `default`: Default message for the feature if no restriction-specific message exists.
 *   - `route`: Route name (e.g., 'admin.profile.update') to apply restrictions via middleware.
 *   - `restrictions`: Optional array of restricted keys (e.g., 'site_settings' with sub-keys like 'site_name').
 *   - `messages`: Optional array of restriction-specific messages (e.g., 'site_settings' => 'Custom message').
 *
 * Messaging Hierarchy:
 * - Restriction-specific message (feature.messages[key]) is used if defined.
 * - Feature default message (feature.default) is used if no restriction-specific message exists.
 * - Global message (messages.global) is used as the final fallback.
 *
 * Database Reset:
 * - If `enabled` is true, the database is reset using the SQL file at `storage_path('../resources/database/database.sql')`.
 * - Resets occur based on `database_reset_unit` and `database_reset_duration` (e.g., every 4 hours).
 * - Last reset time is tracked in `storage/demo_reset.json`.
 *
 * Usage:
 * - Features with `enabled => true` and no `restrictions` block all updates on the specified `route`.
 * - Features with `restrictions` filter specified keys, allowing non-restricted keys to proceed.
 * - Middleware (`demo.restrict:<feature>`) applies restrictions to routes.
 */

return [
    'enabled' => env('APP_MODE', 'live') == "demo",
    'messages' => [
        'global' => 'This is a demo environment. Some actions are restricted.',
    ],
    
    'database_reset_unit' => 'second',
    'database_reset_duration' => 4,

    'feature' => [
        'settings' => [
            'enabled' => true,
            'default' => 'Demo Mode restrictions.',
            'route' => 'admin.system.setting.store',
            'restrictions' => [
                "site_settings" => [
                    // General -> Core Settings
                    "site_name",
                    "copyright",
                    "phone",
                    "email",
                    "address",
                    "app_link",
                    "primary_color",
                    "primary_text_color",
                    "secondary_color",
                    "trinary_color",
                    "site_logo",
                    "site_square_logo",
                    "panel_logo",
                    "favicon",
                    "meta_image",
                    "time_zone",
                    "country_code",
                    "debug_mode",
                    "landing_page",
                    "maintenance_mode",
                    //Automation
                    "queue_connection_config",
                    // General -> Other Settings
                    "max_file_size",
                    "max_file_upload",
                    "mime_types",
                    // Authentication
                    "auth_heading",
                    "authentication_background",
                    "authentication_background_inner_image_one",
                    "authentication_background_inner_image_two",
                    // Member -> Authentication Settings
                    "member_authentication" => [
                        "registration",
                        "login",
                    ],
                    // Plugins
                    "available_plugins",
                    // SEO Settings
                    "meta_title",
                    "meta_keywords",
                    "meta_description",
                ]
            ],
            'messages' => [
            ],
        ],
        'admin_profile' => [
            'enabled'   => true,
            'default'   => 'Demo Mode restrictions.',
            'route'     => 'admin.profile.update',
        ],
        'admin_password' => [
            'enabled'   => true,
            'default'   => 'Demo Mode restrictions.',
            'route'     => 'admin.password.update',
        ],
        'frontend_section' => [
            'enabled'   => true,
            'default'   => 'Demo Mode restrictions.',
            'route'     => 'admin.frontend.sections.save.content',
        ],
        'whatsapp_server' => [
            'enabled'   => true,
            'default'   => 'Demo Mode restrictions.',
            'route'     => 'admin.gateway.whatsapp.device.server.update',
        ],
        'install_update' => [
            'enabled'   => true,
            'default'   => 'Demo Mode restrictions.',
            'route'     => 'admin.system.install.update',
        ],
        'system_update' => [
            'enabled'   => true,
            'default'   => 'Demo Mode restrictions.',
            'route'     => 'admin.system.update',
        ],
    ],
];