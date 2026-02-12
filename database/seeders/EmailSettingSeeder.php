<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vanguard\Models\EmailSettings;

class EmailSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!EmailSettings::where('email_code', '=', 'backup_is_created')->count()) {
            EmailSettings::create([
                'email_code' => 'backup_is_created',
                'scenario' => 'Backup created.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'stim_view_request_feedback')->count()) {
            EmailSettings::create([
                'email_code' => 'stim_view_request_feedback',
                'scenario' => 'Request feedback from stim app view.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'user_invitation')->count()) {
            EmailSettings::create([
                'email_code' => 'user_invitation',
                'scenario' => 'Invite user.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'confirm_code_to_user')->count()) {
            EmailSettings::create([
                'email_code' => 'confirm_code_to_user',
                'scenario' => 'Confirmation code to registered user.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '',
                'bcc' => '',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'registered_notif_to_admin')->count()) {
            EmailSettings::create([
                'email_code' => 'registered_notif_to_admin',
                'scenario' => 'User registered notification to admin.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '',
                'bcc' => '',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'confirmed_notif_to_admin')->count()) {
            EmailSettings::create([
                'email_code' => 'confirmed_notif_to_admin',
                'scenario' => 'Confirmed notification to admin.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '',
                'bcc' => '',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'confirm_thank_to_user')->count()) {
            EmailSettings::create([
                'email_code' => 'confirm_thank_to_user',
                'scenario' => 'Registration confirmed notification to user.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '',
                'bcc' => '',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'password_reset')->count()) {
            EmailSettings::create([
                'email_code' => 'password_reset',
                'scenario' => 'Change password for user.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '',
                'bcc' => '',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'alert_notification')->count()) {
            EmailSettings::create([
                'email_code' => 'alert_notification',
                'scenario' => 'Notification from alert addon.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'dcr_notification')->count()) {
            EmailSettings::create([
                'email_code' => 'dcr_notification',
                'scenario' => 'Notification from DCR.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'email_from_addon')->count()) {
            EmailSettings::create([
                'email_code' => 'email_from_addon',
                'scenario' => 'Notification from email addon.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'transfer_add')->count()) {
            EmailSettings::create([
                'email_code' => 'transfer_add',
                'scenario' => 'Payments: transferred TO user.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'transfer_deduct')->count()) {
            EmailSettings::create([
                'email_code' => 'transfer_deduct',
                'scenario' => 'Payments: transferred FROM user.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'credit_pay')->count()) {
            EmailSettings::create([
                'email_code' => 'credit_pay',
                'scenario' => 'Payments: user added credits.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'credit_deduct')->count()) {
            EmailSettings::create([
                'email_code' => 'credit_deduct',
                'scenario' => 'Payments: subscription payed by credits.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'subscription_pay')->count()) {
            EmailSettings::create([
                'email_code' => 'subscription_pay',
                'scenario' => 'Payments: subscription payed by money.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '$subject',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'credit_balance_low')->count()) {
            EmailSettings::create([
                'email_code' => 'credit_balance_low',
                'scenario' => 'Credit balance low (not sufficient for next renewal).',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => 'Your credit balance is low.',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'credit_card_expiring')->count()) {
            EmailSettings::create([
                'email_code' => 'credit_card_expiring',
                'scenario' => 'Credit Card is expiring within next renewal date.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => 'Your Credit Card is expiring.',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'auto_renewal_off')->count()) {
            EmailSettings::create([
                'email_code' => 'auto_renewal_off',
                'scenario' => '"Auto-Renewal" Off.',
                'sender_name' => '',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '"Auto Renewal" is OFF. Turn ON to avoid service interruption.',
            ]);
        }

        if (!EmailSettings::where('email_code', '=', 'email_2fa_auth')->count()) {
            EmailSettings::create([
                'email_code' => 'email_2fa_auth',
                'scenario' => '2FA auth via email.',
                'sender_name' => 'TablDA No-Reply',
                'sender_email' => '',
                'to' => '$to',
                'cc' => '$cc',
                'bcc' => '$bcc',
                'subject' => '2FA Code',
            ]);
        }

    }
}
