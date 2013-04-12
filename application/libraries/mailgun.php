<?php

class Mailgun {
	function __construct($config) {
		$this->config = $config;
	}
	function send_complex_message($to, $cc, $subject, $text, $html) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $this->config[":apikey"]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v2/'.$this->config[":domain"].'/messages');
		$bcc = $this->config[":bcc"];
		$beans = R::findAll("operator");
		foreach ($beans as $bean){
			if ($bean->email){
				array_push($bcc, $bean->email);
			}
		}
		$data = array(
			'from' => $this->config[":from"],
			'to' => $to,
			// 'cc' => $cc,
			'bcc' => $bcc,
			'subject' => $subject,
			'text' => $text,
			'html' => $html
		);
		if ($cc) {
			$data["cc"] = $cc;
		}
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}
}
