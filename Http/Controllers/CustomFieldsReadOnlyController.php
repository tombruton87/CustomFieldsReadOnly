<?php
// SPDX-License-Identifier: AGPL-3.0-or-later
// Copyright (C) 2024 Tom Bruton

namespace Modules\CustomFieldsReadOnly\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomFieldsReadOnlyController extends Controller
{
    /**
     * Handle admin AJAX actions.
     */
    public function ajaxAdmin(Request $request)
    {
        $response = [
            'status' => 'error',
            'msg'    => '',
        ];

        switch ($request->action) {

            case 'set_readonly':

                $custom_field_id = (int) $request->custom_field_id;
                $readonly        = (bool) $request->readonly;

                if (!$custom_field_id) {
                    $response['msg'] = __('Custom Field not found');
                    break;
                }

                try {
                    $updated = \DB::table('custom_fields')
                        ->where('id', $custom_field_id)
                        ->update(['readonly' => $readonly]);

                    if ($updated === false) {
                        $response['msg'] = __('Custom Field not found');
                    } else {
                        $response['status']      = 'success';
                        $response['msg_success'] = $readonly
                            ? __('Field set to API only')
                            : __('Field set to editable');
                    }
                } catch (\Exception $e) {
                    $response['msg'] = __('Error updating field');
                }
                break;

            case 'set_hide_from_ui':

                $custom_field_id = (int) $request->custom_field_id;
                $hide_from_ui    = (bool) $request->hide_from_ui;

                if (!$custom_field_id) {
                    $response['msg'] = __('Custom Field not found');
                    break;
                }

                try {
                    $updated = \DB::table('custom_fields')
                        ->where('id', $custom_field_id)
                        ->update(['hide_from_ui' => $hide_from_ui]);

                    if ($updated === false) {
                        $response['msg'] = __('Custom Field not found');
                    } else {
                        $response['status']      = 'success';
                        $response['msg_success'] = $hide_from_ui
                            ? __('Field hidden from ticket view')
                            : __('Field visible in ticket view');
                    }
                } catch (\Exception $e) {
                    $response['msg'] = __('Error updating field');
                }
                break;

            default:
                $response['msg'] = 'Unknown action';
                break;
        }

        if ($response['status'] === 'error' && empty($response['msg'])) {
            $response['msg'] = 'Unknown error occurred';
        }

        return \Response::json($response);
    }
}
