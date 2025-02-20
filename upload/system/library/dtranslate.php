<?php

class Dtranslate {
    private $api_url = 'https://api-free.deepl.com/v2/';
	private $api_key = '';
	
	/**
     * @param $key
     */
    public function setApikey($key) {
        $this->api_key = $key;
    }	
	
	public function translate($data) {
		$data['uri'] = 'translate';
		
		$result = $this->getApiData($data);

		return $result;
	}
	
	public function getLanguages($data = array(), $method = 'GET') {
		$data['uri'] = 'languages?type=target';
		
		$result = $this->getApiData($data);

		return $result;
	}
	
	private function getApiData($data, $method = 'POST') {
		$result = array();
		
		$ch = curl_init($this->api_url . $data['uri']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		}
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/x-www-form-urlencoded',
			'Authorization: DeepL-Auth-Key ' . $this->api_key,
		]);

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			$result['error'] = curl_error($ch);
		} else {
			$result['success'] = json_decode($response, true);
		}

		curl_close($ch);
		
		return $result;
	}	
}