<?php
// SPDX-License-Identifier: AGPL-3.0-or-later
// Copyright (C) 2024 Tom Bruton

namespace Modules\CustomFieldsReadOnly\Http\Middleware;

use Closure;

class ReadOnlyFieldsMiddleware
{
    /**
     * Strip readonly custom fields from the web UI save request.
     *
     * API requests go through different routes and are unaffected.
     */
    public function handle($request, Closure $next)
    {
        if (
            \Route::currentRouteName() !== 'mailboxes.custom_fields.ajax'
            || $request->input('action') !== 'save_fields'
        ) {
            return $next($request);
        }

        try {
            $readonly_ids = \Cache::remember('cfro_protected_ids', 300, function () {
                return \DB::table('custom_fields')
                    ->where(function ($q) {
                        $q->where('readonly', true)->orWhere('hide_from_ui', true);
                    })
                    ->pluck('id')
                    ->map(fn($id) => (string) $id)
                    ->toArray();
            });

            if (!empty($readonly_ids)) {
                $fields = $request->input('fields', []);

                foreach (array_keys($fields) as $key) {
                    // Keys may look like "123" or "123[" (multiselect notation).
                    $field_id = rtrim((string) $key, '[');
                    if (in_array($field_id, $readonly_ids, true)) {
                        unset($fields[$key]);
                    }
                }

                $request->merge(['fields' => $fields]);
            }
        } catch (\Exception $e) {
            \Log::warning('CustomFieldsReadOnly: middleware failed to load readonly field ids', ['error' => $e->getMessage()]);
        }

        return $next($request);
    }
}
