/**
 * Custom Fields Read Only - module JS
 */

/**
 * Initialise the admin page toggles.
 * Called from the 'javascript' Eventy hook after the page scripts load.
 */
function initCustomFieldsReadOnlyAdmin()
{
    $(document).ready(function () {

        // Inject both toggles into every existing field panel.
        $('.custom-field-form').each(function () {
            cfroInjectAdminToggles($(this));
        });

        // Auto-save when either toggle changes.
        $(document).on('change', '.cfro-readonly-checkbox', function () {
            var checkbox    = $(this);
            var field_id    = checkbox.attr('data-field-id');
            var is_readonly = checkbox.is(':checked') ? 1 : 0;

            if (typeof cfroAdminAjaxUrl === 'undefined') return;

            fsAjax(
                { action: 'set_readonly', custom_field_id: field_id, readonly: is_readonly },
                cfroAdminAjaxUrl,
                function (response) { showAjaxResult(response); }
            );
        });

        $(document).on('change', '.cfro-hide-ui-checkbox', function () {
            var checkbox     = $(this);
            var field_id     = checkbox.attr('data-field-id');
            var hide_from_ui = checkbox.is(':checked') ? 1 : 0;

            if (typeof cfroAdminAjaxUrl === 'undefined') return;

            fsAjax(
                { action: 'set_hide_from_ui', custom_field_id: field_id, hide_from_ui: hide_from_ui },
                cfroAdminAjaxUrl,
                function (response) { showAjaxResult(response); }
            );
        });
    });
}

/**
 * Inject the "API Only" and "Hide from Ticket View" toggles into one admin field form.
 */
function cfroInjectAdminToggles(form)
{
    var field_id = form.attr('data-custom_field_id');
    if (!field_id) return;

    // Avoid double-injection.
    if (form.find('.cfro-readonly-group').length) return;

    var is_readonly  = (typeof cfroFieldReadonly !== 'undefined' && cfroFieldReadonly[field_id])  ? true : false;
    var is_hidden    = (typeof cfroFieldHideUi   !== 'undefined' && cfroFieldHideUi[field_id])    ? true : false;

    var label_api    = (typeof cfroLabelApiOnly    !== 'undefined') ? cfroLabelApiOnly    : 'API Only';
    var label_hide   = (typeof cfroLabelHideFromUi !== 'undefined') ? cfroLabelHideFromUi : 'Hide from Ticket View';

    var html = cfroToggleHtml('cfro-readonly-checkbox', 'cfro_readonly',  field_id, label_api,  is_readonly,  'cfro-readonly-group')
             + cfroToggleHtml('cfro-hide-ui-checkbox',  'cfro_hide_ui',   field_id, label_hide, is_hidden,    'cfro-hide-ui-group');

    // Insert before the Save/Delete button row.
    var insert_before = form.find('.form-group.margin-top').first();
    if (insert_before.length) {
        insert_before.before(html);
    } else {
        var last_group = form.find('.form-group').last();
        if (last_group.length) last_group.before(html);
        else form.append(html);
    }
}

/**
 * Build an on/off toggle row.
 */
function cfroToggleHtml(css_class, input_name, field_id, label, checked, group_class)
{
    var checked_attr = checked ? 'checked="checked"' : '';
    var uid          = input_name + '_' + field_id;
    return '<div class="form-group ' + group_class + '">' +
               '<label class="col-sm-2 control-label">' + label + '</label>' +
               '<div class="col-sm-10"><div class="controls"><div class="onoffswitch-wrap"><div class="onoffswitch">' +
               '<input type="checkbox" name="' + input_name + '" value="1"' +
                   ' id="' + uid + '"' +
                   ' class="onoffswitch-checkbox ' + css_class + '"' +
                   ' data-field-id="' + field_id + '" ' + checked_attr + '>' +
               '<label class="onoffswitch-label" for="' + uid + '"></label>' +
               '</div></div></div></div>' +
           '</div>';
}

/**
 * Handle readonly and hidden fields in the conversation view.
 * Called from the 'javascript' Eventy hook after initCustomFields().
 */
function initCustomFieldsReadOnly()
{
    $(document).ready(function () {

        // Hidden fields: remove from DOM entirely so they take no space.
        if (typeof cfroHiddenIds !== 'undefined' && cfroHiddenIds.length) {
            cfroHiddenIds.forEach(function (field_id) {
                $('#custom-field-' + field_id).hide();
            });
        }

        // Readonly (but visible) fields: disable inputs and add badge.
        if (typeof cfroReadonlyIds !== 'undefined' && cfroReadonlyIds.length) {
            var label = (typeof cfroLabelApiOnly !== 'undefined') ? cfroLabelApiOnly : 'API Only';

            cfroReadonlyIds.forEach(function (field_id) {
                // Skip fields that are also hidden.
                if (typeof cfroHiddenIds !== 'undefined' && cfroHiddenIds.indexOf(field_id) !== -1) {
                    return;
                }

                var field_el = $('#custom-field-' + field_id);
                if (!field_el.length) return;

                field_el.addClass('cfro-readonly');

                field_el.find(':input')
                    .prop('disabled', true)
                    .prop('required', false)
                    .removeAttr('required');

                field_el.find('.select2-container').addClass('select2-container--disabled');

                field_el.find('.text-help').append(
                    ' <span class="cfro-badge label label-default">' + label + '</span>'
                );
            });
        }
    });
}
