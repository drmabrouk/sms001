jQuery(document).ready(function ($) {
    'use strict';

    // Mobile Sidebar Toggle
    $('#sms-mobile-toggle-btn').on('click', function () {
        $('#sms-global-sidebar').toggleClass('open');
    });

    // Password Visibility Toggle Eyeball
    $(document).on('click', '.sms-password-toggle-btn', function (e) {
        e.preventDefault();
        var targetId = $(this).data('target');
        var $input = $('#' + targetId);
        if ($input.length === 0) return;

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $(this).css('color', 'var(--sms-dark)');
        } else {
            $input.attr('type', 'password');
            $(this).css('color', 'var(--sms-text-muted)');
        }
    });

    // Profile Trigger Navigation
    $('#sms-profile-trigger').on('click', function () {
        var url = new URL(window.location.href);
        url.searchParams.set('tab', 'profile');
        window.location.href = url.toString();
    });

    // Floating Field Helper Syncing
    function syncFloatingLabels() {
        $('.sms-floating-field select').each(function () {
            if ($(this).val() !== '' && $(this).val() !== null) {
                $(this).addClass('has-value');
            } else {
                $(this).removeClass('has-value');
            }
        });
    }
    syncFloatingLabels();
    $(document).on('change', '.sms-floating-field select', syncFloatingLabels);

    // Save Profile Form via AJAX
    $('#sms-profile-form').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=sms_save_profile&security=' + sms_vars.nonce;

        $.post(sms_vars.ajax_url, formData, function (res) {
            if (res.success) {
                alert(res.data.message);
                location.reload();
            } else {
                alert(res.data.message || 'حدث خطأ أثناء حفظ الملف الشخصي');
            }
        });
    });

    // Dynamic User Search, Filter and Sorting
    var userFilterTimeout;
    function fetchFilteredUsers() {
        var searchVal = $('#sms-user-search-input').val();
        var roleVal = $('#sms-user-filter-role').val();
        var instVal = $('#sms-user-filter-institution').val();
        var statusVal = $('#sms-user-filter-status').val();
        var sortVal = $('#sms-user-sort-order').val();

        $.post(sms_vars.ajax_url, {
            action: 'sms_filter_users',
            security: sms_vars.nonce,
            search: searchVal,
            role: roleVal,
            institution: instVal,
            status: statusVal,
            sort: sortVal
        }, function (res) {
            if (res.success) {
                $('#sms-user-cards-container').html(res.data.html);
            }
        });
    }

    $(document).on('input', '#sms-user-search-input', function () {
        clearTimeout(userFilterTimeout);
        userFilterTimeout = setTimeout(fetchFilteredUsers, 300);
    });

    $(document).on('change', '#sms-user-filter-role, #sms-user-filter-institution, #sms-user-filter-status, #sms-user-sort-order', function () {
        fetchFilteredUsers();
    });

    // User Modal Lifecycle
    var $modalUser = $('#sms-modal-user');
    $('#sms-btn-add-user').on('click', function () {
        $('#sms-form-user')[0].reset();
        $('#usr_id').val('0');
        $('#sms-modal-user-title').text('إضافة مستخدم جديد');
        $modalUser.addClass('open');
    });

    $('#sms-modal-user-close').on('click', function () {
        $modalUser.removeClass('open');
    });

    $(document).on('click', '.sms-btn-edit-user', function () {
        $('#usr_id').val($(this).data('id'));
        $('#usr_username').val($(this).data('username'));
        $('#usr_email').val($(this).data('email'));
        $('#usr_firstname').val($(this).data('firstname'));
        $('#usr_lastname').val($(this).data('lastname'));
        $('#usr_role').val($(this).data('role'));
        $('#usr_mem_num').val($(this).data('memnum'));
        $('#usr_mem_val').val($(this).data('memval'));

        var insts = $(this).data('insts');
        $('#usr_insts').val(insts);

        $('#sms-modal-user-title').text('تعديل المستخدم');
        syncFloatingLabels();
        $modalUser.addClass('open');
    });

    $('#sms-form-user').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=sms_save_user&security=' + sms_vars.nonce;

        $.post(sms_vars.ajax_url, formData, function (res) {
            if (res.success) {
                alert(res.data.message);
                location.reload();
            } else {
                alert(res.data.message || 'حدث خطأ أثناء حفظ المستخدم');
            }
        });
    });

    // Student Modal Lifecycle
    var $modalStudent = $('#sms-modal-student');
    $('#sms-btn-add-student').on('click', function () {
        $('#sms-form-student')[0].reset();
        $('#st_id').val('0');
        $('#sms-modal-student-title').text('إضافة طالب جديد');
        $modalStudent.addClass('open');
    });

    $('#sms-modal-student-close').on('click', function () {
        $modalStudent.removeClass('open');
    });

    $(document).on('click', '.sms-btn-edit-student', function () {
        var st = $(this).data('student');
        $('#st_id').val(st.id);
        $('#st_first_name').val(st.first_name);
        $('#st_last_name').val(st.last_name);
        $('#st_gender').val(st.gender);
        $('#st_institution_id').val(st.institution_id);
        $('#st_grade').val(st.grade);
        $('#st_class_section').val(st.class_section);
        $('#st_parent_phone').val(st.parent_phone);
        $('#st_parent_email').val(st.parent_email);
        $('#st_is_active_account').val(st.is_active_account);
        $('#st_membership_number').val(st.membership_number);
        $('#st_membership_validity').val(st.membership_validity);
        $('#st_health_status').val(st.health_status);
        $('#st_preferred_sports').val(st.preferred_sports);

        $('#sms-modal-student-title').text('تعديل بيانات الطالب');
        syncFloatingLabels();
        $modalStudent.addClass('open');
    });

    $('#sms-form-student').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=sms_save_student&security=' + sms_vars.nonce;

        $.post(sms_vars.ajax_url, formData, function (res) {
            if (res.success) {
                alert(res.data.message);
                location.reload();
            } else {
                alert(res.data.message || 'حدث خطأ أثناء حفظ بيانات الطالب');
            }
        });
    });

    // Institutions Modal Lifecycle
    var $modalInst = $('#sms-modal-inst');
    $('#sms-btn-add-inst').on('click', function () {
        $('#sms-form-inst')[0].reset();
        $('#inst_id').val('0');
        $('#sms-modal-inst-title').text('إضافة مؤسسة جديدة');
        $modalInst.addClass('open');
    });

    $('#sms-modal-inst-close').on('click', function () {
        $modalInst.removeClass('open');
    });

    $(document).on('click', '.sms-btn-edit-inst', function () {
        var data = $(this).data('inst');
        $('#inst_id').val(data.id);
        $('#inst_name').val(data.name);
        $('#inst_code').val(data.code);
        $('#inst_type').val(data.type);
        $('#inst_city').val(data.city);
        $('#inst_phone').val(data.phone);
        $('#inst_address').val(data.address);
        $('#sms-modal-inst-title').text('تعديل المؤسسة');
        syncFloatingLabels();
        $modalInst.addClass('open');
    });

    // Import Institutions Modal
    var $modalImportInst = $('#sms-modal-import-inst');
    $('#sms-btn-import-inst').on('click', function () {
        $('#sms-form-import-inst')[0].reset();
        $modalImportInst.addClass('open');
    });

    $('#sms-modal-import-inst-close').on('click', function () {
        $modalImportInst.removeClass('open');
    });

    $('#sms-form-import-inst').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'sms_import_institutions');
        formData.append('security', sms_vars.nonce);

        $.ajax({
            url: sms_vars.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.success) {
                    alert(res.data.message);
                    location.reload();
                } else {
                    alert(res.data.message || 'حدث خطأ أثناء استيراد البيانات');
                }
            }
        });
    });

    // Save Institution Form via AJAX
    $('#sms-form-inst').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=sms_save_institution&security=' + sms_vars.nonce;

        $.post(sms_vars.ajax_url, formData, function (res) {
            if (res.success) {
                alert(res.data.message);
                location.reload();
            } else {
                alert(res.data.message || 'حدث خطأ أثناء حفظ المؤسسة');
            }
        });
    });

    // Delete Institution via AJAX
    $(document).on('click', '.sms-btn-del-inst', function () {
        if (!confirm('هل أنت تأكد من رغبتك في حذف هذه المؤسسة؟')) return;
        var id = $(this).data('id');

        $.post(sms_vars.ajax_url, {
            action: 'sms_delete_institution',
            security: sms_vars.nonce,
            id: id
        }, function (res) {
            if (res.success) {
                alert(res.data.message);
                location.reload();
            } else {
                alert(res.data.message || 'حدث خطأ أثناء حذف المؤسسة');
            }
        });
    });

    // Export Institutions CSV Trigger
    $('#sms-btn-export-inst').on('click', function () {
        window.location.href = sms_vars.export_url;
    });

    // Toggle User Status via AJAX
    $(document).on('click', '.sms-btn-toggle-status', function () {
        var id = $(this).data('id');
        var status = $(this).data('status');

        $.post(sms_vars.ajax_url, {
            action: 'sms_toggle_user_status',
            security: sms_vars.nonce,
            id: id,
            status: status
        }, function (res) {
            if (res.success) {
                alert(res.data.message);
                location.reload();
            } else {
                alert(res.data.message || 'حدث خطأ أثناء تعديل حالة المستخدم');
            }
        });
    });
});
