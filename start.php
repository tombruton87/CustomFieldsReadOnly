<?php
// SPDX-License-Identifier: AGPL-3.0-or-later
// Copyright (C) 2024 Tom Bruton

if (!app()->routesAreCached()) {
    require __DIR__ . '/Http/routes.php';
}
