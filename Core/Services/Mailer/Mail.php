<?php

namespace Core\Services\Mailer;

const CACHE_MAILKEY_TPL = 'bb_sendmail';
const MAIL_USER = 'noreply@bitcoinbot.pro';

class Templates {
    const WITHDRAWAL = 'withdrawal';
}

class MailerAdapter {
    private static function makeTemplate($name, $params): string {
        $content = file_get_contents('./templates/' . $name);
        return str_replace(array_keys($params), array_values($params), $content);
    }

    private static function makeWrapperTemplate($subject, $content) {
        return str_replace([
            '{subject}',
            '{content}'
        ], [
            $subject,
            $content
        ], file_get_contents('./templates/index.html'));
    }

    private static function makeHeaders($email, $subject): string {
        $subject = 'BitcoinBot: ' . $subject;

        $headers  = "From: " . MAIL_USER . "\r\n";
        $headers .= "To: " . $email . "\r\n";
        $headers .= "Subject: " . $subject . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Return-Path: " . MAIL_USER . "\r\n";
        $headers .= "Error-to: " . MAIL_USER . "\r\n";

        return $headers;
    }

    public static function send($email, $subject, $template, $params = []) {
        $headers = self::makeHeaders($email, $subject);
        $content = self::makeTemplate($template, $params);

        $body = self::makeWrapperTemplate($subject, $content);

        RedisAdapter::shared()->rawCommand(
            'JSON.ARRINSERT',
            CACHE_MAILKEY_TPL,
            '.',
            '0',
            json_encode([
                'to' => $email,
                'body' => $headers . "\r\n" . $body,
            ])
        );
    }
}
