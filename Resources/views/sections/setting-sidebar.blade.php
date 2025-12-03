@if (user()->permission('manage_audit_settings') != 'none' && in_array(\Modules\Audit\Entities\AuditSetting::MODULE_NAME, user_modules()))
    <x-setting-menu-item :active="$activeMenu" menu="audit_settings" :href="route('audit-settings.index')"
                         :text="__('audit::app.auditSettings')"/>
@endif
