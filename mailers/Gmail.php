<?php

namespace curlMail\mailers;

class Gmail
{
    private static $instance;
    private $mail, $email, $pass;

    private function __construct($email, $pass)
    {
        $this->email = $email;
        $this->pass = $pass;
    }
    public function __clone()
    {
        trigger_error("Operación Invalida: No puedes clonar una instancia de " . __CLASS__ . " class.", E_USER_ERROR);
    }
    public static function getInstance(string $email, string $passwd): self
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static($email, $passwd);
        }
        return self::$instance;
    }
    private function mailGen(): iterable
    {
        $from = yield;
        $to = yield;
        $subject = yield;
        $body = yield;
        yield "FROM: <" . $from . ">\n";
        yield "To: <" . $to . ">\n";
        yield "Date: " . date("r") . "\n";
        yield "Subject: " . $subject . "\n";
        yield "\n";
        yield $body;
        yield "";
    }

    public function getLine(): string
    {
        $resp = $this->mail->current();
        $this->mail->next();
        return $resp;
    }

    public function send(string $to, string $subject, string $body): string
    {
        $this->mail = $this->mailGen();
        $this->mail->send($this->email);
        $this->mail->send($to);
        $this->mail->send($subject);
        $this->mail->send($body);

        $ch = curl_init("smtps://smtp.gmail.com:465");

        curl_setopt($ch, CURLOPT_MAIL_FROM, "<" . $this->email . ">");
        curl_setopt($ch, CURLOPT_MAIL_RCPT, array("<" . $to . ">"));
        curl_setopt($ch, CURLOPT_USERNAME, $this->email);
        curl_setopt($ch, CURLOPT_PASSWORD, $this->pass);
        curl_setopt($ch, CURLOPT_USE_SSL, CURLUSESSL_ALL);
        curl_setopt($ch, CURLOPT_PUT, 1); // If this option is not activated, $this->getLine will not be executed never
        curl_setopt($ch, CURLOPT_VERBOSE, true); // Uncomment to see the transaction
        curl_setopt($ch, CURLOPT_READFUNCTION, array($this, "getLine"));

        return curl_exec($ch);
    }

}
