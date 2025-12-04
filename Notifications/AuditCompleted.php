<?php

namespace Modules\Audit\Notifications;

use App\Notifications\BaseNotification;
use Modules\Audit\Entities\Audit;

class AuditCompleted extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $audit;

    public function __construct($audit)
    {
        $this->audit = $audit;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if ($notifiable->email_notifications) {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $auditMail = parent::build($notifiable);
        $company = company();
        $url = route('audits.show', $this->audit->id);
        $url = getDomainSpecificUrl($url, $company->id);

        // Load audit with all necessary relationships for PDF generation
        $audit = Audit::with([
            'template',
            'department',
            'auditor',
            'auditee',
            'responses.checkpoint',
            'responses.files',
        ])->findOrFail($this->audit->id);

        // Generate PDF
        $pdf = app('dompdf.wrapper');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->loadView('audit::audits.pdf.report', ['audit' => $audit]);

        // Create filename
        $filename = 'audit-report-' . $audit->id . '.pdf';

        $content = __('audit::email.auditCompleted.line1', ['score' => $audit->score]) . '<br><br>';
        $content .= __('audit::email.auditCompleted.line2', ['department' => $audit->department?->team_name ?? '--']) . '<br>';
        $content .= __('audit::email.auditCompleted.line3', ['auditor' => $audit->auditor?->name ?? '--']) . '<br>';
        $content .= __('audit::email.auditCompleted.line5', ['auditee' => $audit->auditee?->name ?? '--']) . '<br>';
        $content .= __('audit::email.auditCompleted.line4', ['duration' => $audit->duration_formatted ?? '--']) . '<br>';

        $auditMail->subject(__('audit::email.auditCompleted.subject', ['template' => $this->audit->template?->title ?? '--']))
                ->markdown('mail.email', [
                    'url' => $url,
                    'content' => $content,
                    'themeColor' => $company->header_color,
                    'actionText' => __('audit::email.auditCompleted.actionButton'),
                    'notifiableName' => $notifiable->name
                ]);

        $auditMail->attachData($pdf->output(), $filename . '.pdf');

        return $auditMail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->audit->id,
            'audit_template_id' => $this->audit->audit_template_id,
            'template_title' => $this->audit->template->title,
            'auditor_id' => $this->audit->auditor_id,
            'auditor_name' => $this->audit->auditor?->name,
            'auditee_id' => $this->audit->auditee_id,
            'auditee_name' => $this->audit->auditee?->name,
            'score' => $this->audit->score,
            'department' => $this->audit->department?->team_name,
            'completed_at' => $this->audit->completed_at?->format('Y-m-d H:i:s'),
        ];
    }
}

