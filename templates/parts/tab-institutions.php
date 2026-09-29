<?php
if (!defined('ABSPATH')) {
    exit;
}

$institutions = SMS_Institutions::get_all();
?>
<div class="sms-page-header">
    <h2 class="sms-page-title">إدارة المؤسسات</h2>
    <div style="display: flex; gap: 10px;">
        <button type="button" class="sms-btn sms-btn-outline" id="sms-btn-import-inst">استيراد CSV</button>
        <button type="button" class="sms-btn sms-btn-outline" id="sms-btn-export-inst">تصدير CSV</button>
        <button type="button" class="sms-btn sms-btn-dark" id="sms-btn-add-inst">+ إضافة مؤسسة جديدة</button>
    </div>
</div>

<div class="sms-table-container">
    <table class="sms-table">
        <thead>
            <tr>
                <th>اسم المؤسسة</th>
                <th>الكود</th>
                <th>النوع</th>
                <th>المدينة</th>
                <th>الهاتف</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($institutions)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--sms-text-muted);">لا توجد مؤسسات مسجلة حالياً.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($institutions as $inst): ?>
                    <tr>
                        <td><strong><?php echo esc_html($inst['name']); ?></strong></td>
                        <td><?php echo esc_html($inst['code']); ?></td>
                        <td><?php echo esc_html($inst['type']); ?></td>
                        <td><?php echo esc_html($inst['city']); ?></td>
                        <td><?php echo esc_html($inst['phone']); ?></td>
                        <td>
                            <button type="button" class="sms-btn sms-btn-outline sms-btn-edit-inst" data-inst='<?php echo esc_attr(json_encode($inst)); ?>' style="height: 32px; padding: 0 10px; font-size: 0.8rem;">تعديل</button>
                            <button type="button" class="sms-btn sms-btn-danger sms-btn-del-inst" data-id="<?php echo esc_attr($inst['id']); ?>" style="height: 32px; padding: 0 10px; font-size: 0.8rem;">حذف</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Institution Modal -->
<div class="sms-modal-backdrop" id="sms-modal-inst">
    <div class="sms-modal">
        <div class="sms-modal-header">
            <h3 class="sms-modal-title" id="sms-modal-inst-title">إضافة مؤسسة جديدة</h3>
            <button type="button" class="sms-modal-close" id="sms-modal-inst-close">&times;</button>
        </div>
        <form id="sms-form-inst">
            <input type="hidden" id="inst_id" name="id" value="0" />
            <div class="sms-floating-field">
                <input type="text" id="inst_name" name="name" placeholder=" " required />
                <label for="inst_name">اسم المؤسسة</label>
            </div>
            <div class="sms-grid-2">
                <div class="sms-floating-field">
                    <input type="text" id="inst_code" name="code" placeholder=" " />
                    <label for="inst_code">الكود التعريف المعياري</label>
                </div>
                <div class="sms-floating-field">
                    <input type="text" id="inst_type" name="type" placeholder=" " />
                    <label for="inst_type">النوع (مدرسة / مجمع / نادٍ)</label>
                </div>
            </div>
            <div class="sms-grid-2">
                <div class="sms-floating-field">
                    <input type="text" id="inst_city" name="city" placeholder=" " />
                    <label for="inst_city">المدينة</label>
                </div>
                <div class="sms-floating-field">
                    <input type="text" id="inst_phone" name="phone" placeholder=" " />
                    <label for="inst_phone">رقم الهاتف</label>
                </div>
            </div>
            <div class="sms-floating-field">
                <textarea id="inst_address" name="address" placeholder=" "></textarea>
                <label for="inst_address">العنوان</label>
            </div>
            <div style="margin-top: 20px; text-align: left;">
                <button type="submit" class="sms-btn sms-btn-dark">حفظ</button>
            </div>
        </form>
    </div>
</div>

<!-- Import Institutions CSV Modal -->
<div class="sms-modal-backdrop" id="sms-modal-import-inst">
    <div class="sms-modal">
        <div class="sms-modal-header">
            <h3 class="sms-modal-title">استيراد مؤسسات من ملف CSV</h3>
            <button type="button" class="sms-modal-close" id="sms-modal-import-inst-close">&times;</button>
        </div>
        <form id="sms-form-import-inst" enctype="multipart/form-data">
            <div class="sms-floating-field" style="margin-top: 10px;">
                <input type="file" id="csv_file" name="csv_file" accept=".csv" required style="padding-top:12px;" />
            </div>
            <p style="font-size:0.85rem; color:var(--sms-text-muted);">تنسيق الأعمدة المطلوبة: (ID, الاسم, الكود, النوع, المدينة, العنوان, الهاتف)</p>
            <div style="margin-top: 20px; text-align: left;">
                <button type="submit" class="sms-btn sms-btn-dark">رفع واستيراد</button>
            </div>
        </form>
    </div>
</div>
