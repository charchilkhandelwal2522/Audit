<?php

namespace Modules\Audit\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\Audit\Entities\AuditSetting;

class AuditSettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('audit::app.auditSettings');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(AuditSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Display the settings page.
     */
    public function index()
    {
        $this->managePermission = user()->permission('manage_audit_settings');
        abort_403($this->managePermission != 'all');

        $this->activeSettingMenu = 'audit_settings';

        $this->setting = AuditSetting::where('company_id', company()->id)->first();

        if (!$this->setting) {
            $this->setting = AuditSetting::create([
                'company_id' => company()->id,
                'partial_completion_weight' => 0.50,
                'score_threshold_alert' => 70,
                'send_result_to_manager' => true,
                'send_result_to_auditee' => true,
                'send_result_to_auditor' => true,
                'generate_pdf_report' => true,
            ]);
        }

        return view('audit::audit-settings.index', $this->data);
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $this->managePermission = user()->permission('manage_audit_settings');
        abort_403($this->managePermission != 'all');

        $request->validate([
            'partial_completion_weight' => 'required|numeric|min:0|max:1',
            'score_threshold_alert' => 'required|integer|min:0|max:100',
        ]);

        $setting = AuditSetting::where('company_id', company()->id)->first();

        if (!$setting) {
            $setting = new AuditSetting();
            $setting->company_id = company()->id;
        }

        $setting->partial_completion_weight = $request->partial_completion_weight;
        $setting->score_threshold_alert = $request->score_threshold_alert;
        $setting->send_result_to_manager = $request->has('send_result_to_manager');
        $setting->send_result_to_auditee = $request->has('send_result_to_auditee');
        $setting->send_result_to_auditor = $request->has('send_result_to_auditor');
        $setting->generate_pdf_report = $request->has('generate_pdf_report');
        $setting->save();

        return Reply::success(__('messages.updateSuccess'));
    }
}

