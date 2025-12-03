<?php

namespace Modules\Audit\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Audit\Entities\Audit;

class AuditCompleted extends Notification
{
    use Queueable;

    protected Audit $audit;

    /**
     * Create a new notification instance.
     */
    public function __construct(Audit $audit)
    {
        $this->audit = $audit;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $via = ['database'];

        if ($notifiable->email_notifications) {
            $via[] = 'mail';
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = route('audits.show', $this->audit->id);

        return (new MailMessage)
            ->subject(__('audit::app.notifications.auditCompletedSubject', [
                'template' => $this->audit->template->title
            ]))
            ->greeting(__('email.hello') . ' ' . $notifiable->name . '!')
            ->line(__('audit::app.notifications.auditCompletedLine1', [
                'score' => $this->audit->score
            ]))
            ->line(__('audit::app.notifications.auditCompletedLine2', [
                'department' => $this->audit->department?->team_name ?? '--'
            ]))
            ->line(__('audit::app.notifications.auditCompletedLine3', [
                'auditor' => $this->audit->auditor?->name ?? '--'
            ]))
            ->line(__('audit::app.notifications.auditCompletedLine5', [
                'auditee' => $this->audit->auditee?->name ?? '--'
            ]))
            ->line(__('audit::app.notifications.auditCompletedLine4', [
                'duration' => $this->audit->duration_formatted
            ]))
            ->action(__('audit::app.notifications.viewAuditDetails'), $url);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
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

