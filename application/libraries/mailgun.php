<?php

class Mailgun {
	function __construct($config) {
		$this->config = $config;
	}
	function send_complex_message($to, $cc, $bcc, $subject, $text, $html) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, 'api:key-3ax6xnjp29jd6fds4gc373sgvjxteol0');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v2/'.$this->config[":domain"].'/messages');
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'from' => $this->config[":from"],
			'to' => $to,
			'cc' => $cc,
			'bcc' => $bcc,
			'subject' => $subject,
			'text' => $text,
			'html' => $html
		));

		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}
}
