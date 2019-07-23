/* jshint -W097, -W117 */
/* globals $, document, architect */

'use strict';

var editors = {},
    notyCount = 0;

$.ajaxSetup ({
    cache: false
});

$(document).ready(function()
{
    $('html').addClass('architect');

    if ($('#menu-architect').length === 0) {
        var message = architect.i18n.notify_arch_ocmod + '<br>' + architect.msg_ocmod_refresh;
        notify('danger', message);
    }

    // Refresh editor per tab open
    $('.arc-nav-editor a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        var editor = $('.tab-content .active').find('.CodeMirror')[0];

        if (editor && editor.CodeMirror) {
            setTimeout(function () {
                editor.CodeMirror.refresh();
            }, 100);
        }
    });

    /**
     * IIDE CodeMirror
     *
     * Mode
     * - html mix   : text/html
     * - css        : text/x-scss
     * - javascript : text/javascript
     * - php        : application/x-httpd-php
     * - xml        : application/xml
     *
     * Usage:
     * - <textarea id="editor" data-arc-codemirror='{"mode":"application/x-httpd-php"}'></textarea>
     * - <div data-arc-codemirror='{"id":"editor", "mode":"application/x-httpd-php"}'><textarea id="editor"></textarea></div>
     */
    $('[data-arc-codemirror]').each(function(e) {
        var el = this,
            param = $.extend({
                id   : $(this).attr('id'),
                mode : '',
            }, $(el).data('arc-codemirror'));

        if (param.id === undefined) {
            return false;
        }

        editors[param.id] = CodeMirror.fromTextArea(document.getElementById(param.id), {
            mode            : param.mode,
            indentUnit      : 4,
            lineNumbers     : true,
            lineWrapping    : true,
            styleActiveLine : {nonEmpty: true},
            foldGutter      : true,
            gutters         : ['CodeMirror-linenumbers', 'CodeMirror-foldgutter'],
            matchBrackets   : true,
            matchClosing    : true,
            extraKeys       : {
                'Tab'    : cmSpaceTab,
                'Ctrl-S' : function(instance) {
                    $('.arc-save').trigger('click');
                },
                'Cmd-S'  : function(instance) {
                    $('.arc-save').trigger('click');
                },
            }
        });
    });

    /**
     * Saving module data
     */
    $('.arc-save').on('click', function(e) {
        e.preventDefault();
        $('.arc-alert').trigger('click'); // close all notify

        // Update editor textarea
        $.each(editors, function (key, editor) {
            $('#' + key).val(editor.getDoc().getValue());
        });

        $.ajax({
                type     : 'POST',
                dataType : 'json',
                cache    : false,
                url      : architect.url_module_save.replace('&amp;', '&'),
                data     : $('#form-architect').serialize(),
                beforeSend :    function(xhr, param) {
                    var error = false;

                    // Validate
                    if ($('input[name="name"]').val().length < 3) {
                        error = true;
                        $('input[name="name"]').parents('.required').addClass('has-error');
                        notify('danger beforeSend', architect.i18n.validate_name);
                    }

                    if (error) {
                        return false;
                    }

                    notify('primary beforeSend', architect.i18n.text_processing);
                },
                complete : function(xhr) {
                    $('.beforeSend').trigger('click'); // close .arc-alert.beforeSend
                    $('.required').removeClass('has-error');
                },
                success : function(resp, status, xhr) {
                    if (!resp.error) {
                        $('.module_id').val(resp.module_id);

                        notify('success', architect.i18n.notify_success, true);
                    } else {
                        notify('warning', resp.error);
                    }
                }
        });
    });

    /**
     * Notification, work along with notify() and notifyHide()
     */
    $('.arc-module').on('click', '.arc-alert', function(e) {
        if (e.target.nodeName == 'DIV') {
            $(e.target).fadeOut(400, function() {
                $(this).remove();
            });
        }
    });
});

function notify(type, message, hide) {
    notyCount++;

    $('.arc-notification').prepend('<div class="alert alert-' + type + ' arc-alert arc-noty-' + notyCount + ' fade">' + message + '</div>');
    $('.arc-noty-' + notyCount).fadeTo(200, 1);

    if (hide) {
        notifyHide('.arc-noty-' + notyCount);
    }
}

function notifyHide(el, timeout) {
    var delay = timeout || 2500;

    setTimeout(function () {
        $(el).trigger('click');
    }, delay);
}

/**
 * CodeMirror space indention
 */
function cmSpaceTab(editor) {
    if (editor.somethingSelected()) {
        editor.indentSelection('add');
    } else {
        editor.replaceSelection(editor.getOption('indentWithTabs')? '\t':
            Array(editor.getOption('indentUnit') + 1).join(' '), 'end', '+input');
    }
}
