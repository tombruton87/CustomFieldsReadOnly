<?php
// SPDX-License-Identifier: AGPL-3.0-or-later
// Copyright (C) 2024 Tom Bruton

namespace Modules\CustomFieldsReadOnly\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

define('CFRO_MODULE', 'customfieldsreadonly');

class CustomFieldsReadOnlyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerConfig();
        $this->registerTranslations();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->ensureSchemaExists();

        // Register middleware that strips readonly/hidden fields from web UI saves.
        $this->app['router']->pushMiddlewareToGroup(
            'web',
            \Modules\CustomFieldsReadOnly\Http\Middleware\ReadOnlyFieldsMiddleware::class
        );

        $this->hooks();
    }

    /**
     * Ensure both columns exist, creating them if needed.
     * Makes the module self-installing without requiring artisan migrate.
     */
    protected function ensureSchemaExists()
    {
        try {
            if (!Schema::hasColumn('custom_fields', 'readonly')) {
                Schema::table('custom_fields', function (Blueprint $table) {
                    $table->boolean('readonly')->default(false);
                });
            }
            if (!Schema::hasColumn('custom_fields', 'hide_from_ui')) {
                Schema::table('custom_fields', function (Blueprint $table) {
                    $table->boolean('hide_from_ui')->default(false);
                });
            }
        } catch (\Exception $e) {
            // custom_fields table may not exist yet on a fresh install; safe to ignore.
        }
    }

    public function hooks()
    {
        // Load module CSS.
        \Eventy::addFilter('stylesheets', function ($styles) {
            $styles[] = \Module::getPublicPath(CFRO_MODULE) . '/css/module.css';
            return $styles;
        });

        // Load module JS.
        \Eventy::addFilter('javascripts', function ($javascripts) {
            $javascripts[] = \Module::getPublicPath(CFRO_MODULE) . '/js/module.js';
            return $javascripts;
        });

        // Output JS variables and bootstrap calls.
        \Eventy::addAction('javascript', function () {

            // ── Admin: Custom Fields settings page ───────────────────────
            if (\Route::is('mailboxes.custom_fields')) {

                $mailbox_id = (int) (
                    \Route::current()->parameter('id')
                    ?? request()->route('id')
                    ?? request()->id
                    ?? 0
                );

                try {
                    $ajax_url = route('customfieldsreadonly.ajax_admin');
                } catch (\Exception $e) {
                    $ajax_url = url(ltrim(\Helper::getSubdirectory(), '/') . '/custom-fields-readonly/ajax-admin');
                }
                echo 'var cfroAdminAjaxUrl = ' . json_encode($ajax_url) . ';' . "\n";

                $readonly_map  = [];
                $hide_map      = [];
                if ($mailbox_id) {
                    try {
                        $rows = \DB::table('custom_fields')
                            ->where('mailbox_id', $mailbox_id)
                            ->select('id', 'readonly', 'hide_from_ui')
                            ->get();
                        foreach ($rows as $row) {
                            $readonly_map[(string) $row->id] = (bool) $row->readonly;
                            $hide_map[(string) $row->id]     = (bool) $row->hide_from_ui;
                        }
                    } catch (\Exception $e) {
                        // columns not yet available — leave maps empty.
                    }
                }
                echo 'var cfroFieldReadonly  = ' . json_encode($readonly_map) . ';' . "\n";
                echo 'var cfroFieldHideUi    = ' . json_encode($hide_map) . ';' . "\n";
                echo 'var cfroLabelApiOnly   = ' . json_encode(__('API Only')) . ';' . "\n";
                echo 'var cfroLabelHideFromUi = ' . json_encode(__('Hide from Ticket View')) . ';' . "\n";
                echo 'initCustomFieldsReadOnlyAdmin();' . "\n";
            }

            // ── Conversation view ─────────────────────────────────────────
            if (\Route::is('conversations.view') || \Route::is('conversations.create')) {
                $readonly_ids = [];
                $hidden_ids   = [];
                try {
                    $rows = \DB::table('custom_fields')
                        ->where(function ($q) {
                            $q->where('readonly', true)->orWhere('hide_from_ui', true);
                        })
                        ->select('id', 'readonly', 'hide_from_ui')
                        ->get();
                    foreach ($rows as $row) {
                        if ($row->readonly)    $readonly_ids[] = $row->id;
                        if ($row->hide_from_ui) $hidden_ids[]  = $row->id;
                    }
                } catch (\Exception $e) {
                    // columns not yet available — leave lists empty.
                }
                echo 'var cfroReadonlyIds = ' . json_encode($readonly_ids) . ';' . "\n";
                echo 'var cfroHiddenIds   = ' . json_encode($hidden_ids) . ';' . "\n";
                echo 'initCustomFieldsReadOnly();' . "\n";
            }
        }, 25);
    }

    public function register()
    {
        $this->registerTranslations();
    }

    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'customfieldsreadonly');
    }

    protected function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ . '/../Resources/lang');
    }

    public function provides()
    {
        return [];
    }
}
