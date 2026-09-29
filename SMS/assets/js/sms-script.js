jQuery(document).ready(function ($) {
    'use strict';

    // In-App Toast Confirmation Engine (Replacing browser alert())
    function showToast(message, type) {
        type = type || 'info';
        var $container = $('#sms-toast-container');
        if ($container.length === 0) {
            $('body').append('<div id="sms-toast-container"></div>');
            $container = $('#sms-toast-container');
        }

        var $toast = $('<div class="sms-toast ' + type + '"><span>' + message + '</span><span class="sms-toast-close" style="cursor:pointer; margin-right:10px;">&times;</span></div>');
        $container.append($toast);

        $toast.find('.sms-toast-close').on('click', function () {
            $toast.remove();
        });

        setTimeout(function () {
            $toast.fadeOut(400, function () { $(this).remove(); });
        }, 4000);
    }

    // Mobile Sidebar Toggle
    $('#sms-mobile-toggle-btn').on('click', function () {
        $('#sms-global-sidebar').toggleClass('open');
    });

    // Notification Center Drawer Toggle & Fetching
    var $notifDrawer = $('#sms-notif-drawer-backdrop');
    function refreshNotifications() {
        $.post(sms_vars.ajax_url, {
            action: 'sms_fetch_notifications',
            security: sms_vars.nonce
        }, function (res) {
            if (res.success) {
                $('.sms-notif-badge').text(res.data.count);
                if (res.data.count > 0) {
                    $('.sms-notif-badge').show();
                } else {
                    $('.sms-notif-badge').hide();
                }
                $('#sms-notif-list-container').html(res.data.html);
            }
        });
    }

    $(document).on('click', '.sms-notif-bell-btn', function () {
        refreshNotifications();
        $notifDrawer.addClass('open');
    });

    $('#sms-notif-drawer-close').on('click', function () {
        $notifDrawer.removeClass('open');
    });

    $(document).on('click', '.sms-btn-mark-read', function () {
        var notifId = $(this).data('id');
        $.post(sms_vars.ajax_url, {
            action: 'sms_mark_read_notification',
            security: sms_vars.nonce,
            id: notifId
        }, function (res) {
            if (res.success) {
                refreshNotifications();
            }
        });
    });

    $('#sms-btn-mark-all-read').on('click', function () {
        $.post(sms_vars.ajax_url, {
            action: 'sms_mark_all_read_notifications',
            security: sms_vars.nonce
        }, function (res) {
            if (res.success) {
                refreshNotifications();
            }
        });
    });

    // 60s Lightweight Notification Sync
    setInterval(function () {
        if (document.visibilityState === 'visible') {
            $.post(sms_vars.ajax_url, {
                action: 'sms_fetch_notifications',
                security: sms_vars.nonce
            }, function (res) {
                if (res.success) {
                    $('.sms-notif-badge').text(res.data.count);
                    if (res.data.count > 0) {
                        $('.sms-notif-badge').show();
                    } else {
                        $('.sms-notif-badge').hide();
                    }
                }
            });
        }
    }, 60000);

    // Password Visibility Toggle
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
        var formData = new FormData(this);
        formData.append('action', 'sms_save_profile');
        formData.append('security', sms_vars.nonce);

        $.ajax({
            url: sms_vars.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.success) {
                    showToast(res.data.message, 'success');
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    showToast(res.data.message || 'حدث خطأ أثناء حفظ الملف الشخصي', 'error');
                }
            }
        });
    });

    // Report Creation & Typeahead Teacher Search
    var teacherSearchTimeout;
    $('#rep_teacher_search').on('input', function () {
        clearTimeout(teacherSearchTimeout);
        var query = $(this).val();
        if (query.length < 2) {
            $('#rep_teacher_typeahead_results').hide().empty();
            return;
        }

        teacherSearchTimeout = setTimeout(function () {
            $.post(sms_vars.ajax_url, {
                action: 'sms_search_teachers',
                security: sms_vars.nonce,
                query: query
            }, function (res) {
                if (res.success && res.data.results.length > 0) {
                    var $box = $('#rep_teacher_typeahead_results').empty().show();
                    $.each(res.data.results, function (i, item) {
                        var $item = $('<div style="padding:8px 12px; cursor:pointer; font-size:0.85rem; border-bottom:1px solid #eee;">' + item.name + '</div>');
                        $item.on('click', function () {
                            $('#rep_teacher_search').val(item.name);
                            $('#rep_teacher_id').val(item.id);
                            $box.hide();
                        });
                        $box.append($item);
                    });
                } else {
                    $('#rep_teacher_typeahead_results').hide();
                }
            });
        }, 300);
    });

    var $modalReport = $('#sms-modal-report');
    $('#sms-btn-create-report').on('click', function () {
        $('#sms-form-create-report')[0].reset();
        $('#rep_teacher_id').val('0');
        $modalReport.addClass('open');
    });

    $('#sms-modal-report-close').on('click', function () {
        $modalReport.removeClass('open');
    });

    $('#sms-form-create-report').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=sms_create_report&security=' + sms_vars.nonce;

        $.post(sms_vars.ajax_url, formData, function (res) {
            if (res.success) {
                showToast(res.data.message, 'success');
                setTimeout(function () { location.reload(); }, 1000);
            } else {
                showToast(res.data.message || 'حدث خطأ أثناء إنشاء التقرير', 'error');
            }
        });
    });

    // Print Report
    $(document).on('click', '.sms-btn-print-report', function () {
        var rep = $(this).data('rep');
        $('#print_doc_inst_name').text('المؤسسة: ' + rep.institution_name);
        $('#print_rep_teacher_name').text(rep.teacher_name);
        $('#print_rep_lesson_title').text(rep.lesson_title);
        $('#print_rep_attendance').text(rep.attendance_status === 'present' ? 'حاضر' : 'غائب');
        $('#print_rep_rating').text(rep.rating + ' / 5');
        $('#print_rep_date').text(rep.created_at);
        $('#print_rep_notes').text(rep.notes ? rep.notes : 'لا توجد ملاحظات إضافية.');

        var printContents = $('#sms-printable-report-area').html();
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>طباعة التقرير</title></head><body dir="rtl">');
        printWindow.document.write(printContents);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    });

    // Submit Semester Plan via AJAX
    var $modalSubmitPlan = $('#sms-modal-submit-plan');
    $('#sms-btn-submit-plan').on('click', function () {
        $('#sms-form-submit-plan')[0].reset();
        $modalSubmitPlan.addClass('open');
    });

    $('#sms-modal-submit-plan-close').on('click', function () {
        $modalSubmitPlan.removeClass('open');
    });

    $('#sms-form-submit-plan').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'sms_submit_semester_plan');
        formData.append('security', sms_vars.nonce);

        $.ajax({
            url: sms_vars.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.success) {
                    showToast(res.data.message, 'success');
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    showToast(res.data.message || 'حدث خطأ أثناء إرسال الخطة الفصلية', 'error');
                }
            }
        });
    });

    // Save Document Information
    $('#sms-form-doc-info').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=sms_save_doc_info&security=' + sms_vars.nonce;
        $.post(sms_vars.ajax_url, formData, function (res) {
            if (res.success) {
                showToast(res.data.message, 'success');
            } else {
                showToast(res.data.message, 'error');
            }
        });
    });

    // Save Typography Font Scale
    $('#sms-form-typography').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=sms_save_typography&security=' + sms_vars.nonce;
        $.post(sms_vars.ajax_url, formData, function (res) {
            if (res.success) {
                showToast(res.data.message, 'success');
                setTimeout(function () { location.reload(); }, 1000);
            } else {
                showToast(res.data.message, 'error');
            }
        });
    });

    // Backup Download
    $('#sms-btn-download-backup').on('click', function () {
        $.post(sms_vars.ajax_url, {
            action: 'sms_download_backup',
            security: sms_vars.nonce
        }, function (res) {
            if (res.success) {
                var blob = new Blob([res.data.json], { type: 'application/json' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'sms_backup_' + new Date().toISOString().slice(0,10) + '.json';
                link.click();
                showToast('تم إعداد وتحميل النسخة الاحتياطية', 'success');
            }
        });
    });

    // Restore Backup
    $('#sms-form-restore-backup').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'sms_restore_backup');
        formData.append('security', sms_vars.nonce);

        $.ajax({
            url: sms_vars.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.success) {
                    showToast(res.data.message, 'success');
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    showToast(res.data.message, 'error');
                }
            }
        });
    });

    // Data Purge
    $('#sms-btn-purge-data').on('click', function () {
        if (!confirm('تأكيد هام جداً: هل أنت متأكد من رغبتك في حذف كافة بيانات النظام والمؤسسات؟ لا يمكن التراجع عن هذه الخطوة.')) return;

        $.post(sms_vars.ajax_url, {
            action: 'sms_purge_data',
            security: sms_vars.nonce
        }, function (res) {
            if (res.success) {
                showToast(res.data.message, 'success');
                setTimeout(function () { location.reload(); }, 1000);
            }
        });
    });

    // Delete & Recover Activity Logs
    $(document).on('click', '.sms-btn-delete-activity', function () {
        var id = $(this).data('id');
        $.post(sms_vars.ajax_url, {
            action: 'sms_delete_activity',
            security: sms_vars.nonce,
            id: id
        }, function (res) {
            if (res.success) {
                showToast(res.data.message, 'success');
                setTimeout(function () { location.reload(); }, 1000);
            }
        });
    });

    $(document).on('click', '.sms-btn-recover-activity', function () {
        var id = $(this).data('id');
        $.post(sms_vars.ajax_url, {
            action: 'sms_recover_activity',
            security: sms_vars.nonce,
            id: id
        }, function (res) {
            if (res.success) {
                showToast(res.data.message, 'success');
                setTimeout(function () { location.reload(); }, 1000);
            }
        });
    });

    // Submit Lesson Preparation via AJAX
    var $modalSubmitLesson = $('#sms-modal-submit-lesson');
    $('#sms-btn-submit-lesson, #sms-btn-quick-submit').on('click', function () {
        $('#sms-form-submit-lesson')[0].reset();
        $modalSubmitLesson.addClass('open');
    });

    $('#sms-modal-submit-lesson-close').on('click', function () {
        $modalSubmitLesson.removeClass('open');
    });

    $('#sms-form-submit-lesson').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'sms_submit_lesson');
        formData.append('security', sms_vars.nonce);

        $.ajax({
            url: sms_vars.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.success) {
                    showToast(res.data.message, 'success');
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    showToast(res.data.message || 'حدث خطأ أثناء إرسال التحضير', 'error');
                }
            }
        });
    });

    // Review Lesson Prep Approval/Rejection
    $(document).on('click', '.sms-btn-review-prep', function () {
        var prepId = $(this).data('id');
        var status = $(this).data('status');

        $.post(sms_vars.ajax_url, {
            action: 'sms_review_prep',
            security: sms_vars.nonce,
            prep_id: prepId,
            status: status
        }, function (res) {
            if (res.success) {
                showToast(res.data.message, 'success');
                setTimeout(function () { location.reload(); }, 1000);
            } else {
                showToast(res.data.message || 'حدث خطأ أثناء المراجعة', 'error');
            }
        });
    });

    // Dynamic Lesson Prep Filtering
    var prepFilterTimeout;
    function fetchFilteredPreps() {
        var searchVal = $('#sms-prep-search-input').val();
        var statusVal = $('#sms-prep-filter-status').val();
        var instVal   = $('#sms-prep-filter-institution').val();

        $.post(sms_vars.ajax_url, {
            action: 'sms_filter_preps',
            security: sms_vars.nonce,
            search: searchVal,
            status: statusVal,
            institution: instVal
        }, function (res) {
            if (res.success) {
                $('#sms-prep-cards-container').html(res.data.html);
            }
        });
    }

    $(document).on('input', '#sms-prep-search-input', function () {
        clearTimeout(prepFilterTimeout);
        prepFilterTimeout = setTimeout(fetchFilteredPreps, 300);
    });

    $(document).on('change', '#sms-prep-filter-status, #sms-prep-filter-institution', function () {
        fetchFilteredPreps();
    });

    // Dynamic Institution Search
    $(document).on('input', '#sms-inst-search-input', function () {
        var val = $(this).val().toLowerCase();
        $('.sms-inst-card').each(function () {
            var text = $(this).data('name').toLowerCase();
            if (text.indexOf(val) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Dynamic User Search, Filter and Sorting
    var userFilterTimeout;
    function fetchFilteredUsers() {
        var searchVal = $('#sms-user-search-input').val();
        var roleVal   = $('#sms-user-filter-role').val();
        var instVal   = $('#sms-user-filter-institution').val();
        var statusVal = $('#sms-user-filter-status').val();
        var sortVal   = $('#sms-user-sort-order').val();

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
                showToast(res.data.message, 'success');
                setTimeout(function () { location.reload(); }, 1000);
            } else {
                showToast(res.data.message || 'حدث خطأ أثناء حفظ المستخدم', 'error');
            }
        });
    });

    // Bulk User CSV Import Modal Lifecycle
    var $modalImportUsers = $('#sms-modal-import-users');
    $('#sms-btn-import-users').on('click', function () {
        $('#sms-form-import-users')[0].reset();
        $modalImportUsers.addClass('open');
    });

    $('#sms-modal-import-users-close').on('click', function () {
        $modalImportUsers.removeClass('open');
    });

    $('#sms-form-import-users').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'sms_import_users');
        formData.append('security', sms_vars.nonce);

        $.ajax({
            url: sms_vars.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.success) {
                    showToast(res.data.message, 'success');
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    showToast(res.data.message || 'حدث خطأ أثناء الاستيراد الجماعي للمستخدمين', 'error');
                }
            }
        });
    });

    // Student Modal Lifecycle
    var $modalStudent = $('#sms-modal-student');
    $('#sms-btn-add-student, #sms-btn-add-student-affairs').on('click', function () {
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
                showToast(res.data.message, 'success');
                setTimeout(function () { location.reload(); }, 1000);
            } else {
                showToast(res.data.message || 'حدث خطأ أثناء حفظ بيانات الطالب', 'error');
            }
        });
    });

    // Student Affairs CSV Import Modal
    var $modalImportStudentsExcel = $('#sms-modal-import-students-excel');
    $('#sms-btn-import-students-excel').on('click', function () {
        $('#sms-form-import-students-excel')[0].reset();
        $modalImportStudentsExcel.addClass('open');
    });

    $('#sms-modal-import-students-excel-close').on('click', function () {
        $modalImportStudentsExcel.removeClass('open');
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
                    showToast(res.data.message, 'success');
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    showToast(res.data.message || 'حدث خطأ أثناء استيراد البيانات', 'error');
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
                showToast(res.data.message, 'success');
                setTimeout(function () { location.reload(); }, 1000);
            } else {
                showToast(res.data.message || 'حدث خطأ أثناء حفظ المؤسسة', 'error');
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
                showToast(res.data.message, 'success');
                setTimeout(function () { location.reload(); }, 1000);
            } else {
                showToast(res.data.message || 'حدث خطأ أثناء حذف المؤسسة', 'error');
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
                showToast(res.data.message, 'success');
                setTimeout(function () { location.reload(); }, 1000);
            } else {
                showToast(res.data.message || 'حدث خطأ أثناء تعديل حالة المستخدم', 'error');
            }
        });
    });
});
