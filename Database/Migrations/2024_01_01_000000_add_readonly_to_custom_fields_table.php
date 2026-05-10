<?php
// SPDX-License-Identifier: AGPL-3.0-or-later
// Copyright (C) 2024 Tom Bruton

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReadonlyToCustomFieldsTable extends Migration
{
    public function up()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_fields', 'readonly')) {
                $table->boolean('readonly')->default(false);
            }
            if (!Schema::hasColumn('custom_fields', 'hide_from_ui')) {
                $table->boolean('hide_from_ui')->default(false);
            }
        });
    }

    public function down()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            if (Schema::hasColumn('custom_fields', 'readonly')) {
                $table->dropColumn('readonly');
            }
            if (Schema::hasColumn('custom_fields', 'hide_from_ui')) {
                $table->dropColumn('hide_from_ui');
            }
        });
    }
}
