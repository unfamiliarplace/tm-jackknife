<?php

/**
 * Class for sending out emails.
 */
class MJKSC_Email {

	private $recipients;
	private $subject;
	private $message;

	/**
	 * Save an array of recipient email addresses, a subject, and a message.
	 *
	 * @param string[] $recipients
	 * @param string $subject
	 * @param string $message
	 */
	function __construct(array $recipients, string $subject, string $message) {
		$this->recipients = $recipients;
		$this->subject = $subject;
		$this->message = $message;
	}

	/**
	 * Send the email. Log if unsuccessful.
	 */
	function send(): void {

		// Standard headers
		$headers = [
			'Return-Path: <web@themedium.ca>',
			'MIME-Version: 1.0\r\n',
			'Content-Type: text/html; charset=ISO-8859-1\r\n'
		];

		// Wrap it in HTML tags for technical compliance... lazy but yes.
		$msg = sprintf('<html><head></head><body>%s</body></html>',
			$this->message);

		// wp_mail returns true or false depends on send success
		$success = wp_mail($this->recipients, $this->subject, $msg, $headers);

		if (!$success) {

			// But it doesn't pass the actual errors, so we have to fetch them
			global $ts_mail_errors;
			global $phpmailer;

			if (!isset($ts_mail_errors)) $ts_mail_errors = [];
			if (isset($phpmailer)) $ts_mail_errors[] = $phpmailer->ErrorInfo;

			error_log(print_r($ts_mail_errors, true));
		}
	}
}
