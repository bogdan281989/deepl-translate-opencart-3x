<?php
class ControllerExtensionModuleDtranslate extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/dtranslate');
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_dtranslate', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['key'])) {
			$data['error_key'] = $this->error['key'];
		} else {
			$data['error_key'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/dtranslate', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/dtranslate', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_dtranslate_status'])) {
			$data['module_dtranslate_status'] = $this->request->post['module_dtranslate_status'];
		} else {
			$data['module_dtranslate_status'] = $this->config->get('module_dtranslate_status');
		}
		
		if (isset($this->request->post['module_dtranslate_api_key'])) {
			$data['module_dtranslate_api_key'] = $this->request->post['module_dtranslate_api_key'];
		} else {
			$data['module_dtranslate_api_key'] = $this->config->get('module_dtranslate_api_key');
		}
		
		if (isset($this->request->post['module_dtranslate_save_formatting'])) {
			$data['module_dtranslate_save_formatting'] = $this->request->post['module_dtranslate_save_formatting'];
		} else {
			$data['module_dtranslate_save_formatting'] = $this->config->get('module_dtranslate_save_formatting');
		}	
		
		if (isset($this->request->post['module_dtranslate_model_type'])) {
			$data['module_dtranslate_model_type'] = $this->request->post['module_dtranslate_model_type'];
		} else {
			$data['module_dtranslate_model_type'] = $this->config->get('module_dtranslate_model_type');
		}
		
		$data['model_types'] = array(
			'quality_optimized' 		=> $this->language->get('text_quality_optimized'),
			'prefer_quality_optimized' 	=> $this->language->get('text_prefer_quality_optimized'),
			'latency_optimized' 		=> $this->language->get('text_latency_optimized')
		);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/dtranslate', $data));
	}
	
	public function getTranslate() {
		$this->load->language('extension/module/dtranslate');
		
		$json = array();
		
		if (isset($this->request->post['text_name']) && isset($this->request->post['text_description'])) {
			
			if(!empty($this->request->post['text_name'])) {
				$json['text_name'] = $this->translateText($this->request->post['text_name'], $this->request->post['code']);
			}
		
			if(!empty($this->request->post['text_description'])) {
				$json['text_description'] = $this->translateText($this->request->post['text_description'], $this->request->post['code']);
			}
		
			if(!empty($json['text_name']['error']) || !empty($json['text_description']['error'])) {
				$json['error'] = sprintf($this->language->get('error_api_data'), !empty($json['text_name']['error']) ? $json['text_name']['error'] : $json['text_description']['error']);
			} else {
				$json['success'] = $this->language->get('text_success_translate');
			}
		} else {
			$json['error'] = $this->language->get('error_select_data');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	private function translateText($text, $targetLang) {
		$this->load->language('extension/module/dtranslate');
		
		$dtranslate = new Dtranslate();
		
		$dtranslate->setApikey($this->config->get('module_dtranslate_api_key'));

		if($this->config->get('module_dtranslate_save_formatting')) {
			$preserve_formatting = true;
		} else {
			$preserve_formatting = false;
		}

		$data = array(
			'text' 					=> $text,
			'tag_handling' 			=> 'html',
			'preserve_formatting' 	=> $preserve_formatting,
			'model_type' 			=> $this->config->get('module_dtranslate_model_type'),
			'target_lang' 			=> $targetLang
		);
		
		$result = $dtranslate->translate($data);
		
		$text = array();

		if(!empty($result['success']['translations'][0]['text'])) {
			$text['success'] = $result['success']['translations'][0]['text'];
		} elseif(!empty($result['success']['message'])) {
			$text['error'] = $result['success']['message'];
		}

		return $text;
	}
	
	private function getLanguages($api_key) {
		$this->load->model('extension/module/dtranslate');
		
		$dtranslate = new Dtranslate();
		
		$dtranslate->setApikey($api_key);
		
		$result = $dtranslate->getLanguages();
		
		if(!empty($result['success'])) {
			$this->model_extension_module_dtranslate->addDtranslateLanguage($result['success']);
			
			return true;
		} else {
			return false;
		}
	}
	
	public function getButtonTranslate() {
		$this->load->language('extension/module/dtranslate');
		
		$this->load->model('extension/module/dtranslate');
		
		$data['status'] = $this->config->get('module_dtranslate_status');
		
		if(isset($this->request->get['language_id'])) {
			$data['language_id'] = $this->request->get['language_id'];
		} else {
			$data['language_id'] = $this->config->get('config_language_id');
		}
		
		$data['translate_language'] = $this->model_extension_module_dtranslate->getTranslateLang();
		
		$this->response->setOutput($this->load->view('extension/module/dtranslate_button', $data));
	}
	
	public function install() {
		$this->load->model('extension/module/dtranslate');
		
		$this->model_extension_module_dtranslate->install();
	}
	
	public function uninstall() {
		$this->load->model('extension/module/dtranslate');
		
		$this->model_extension_module_dtranslate->uninstall();
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/dtranslate')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if(empty($this->request->post['module_dtranslate_api_key']) || !$this->getLanguages($this->request->post['module_dtranslate_api_key'])) {
			$this->error['warning'] = $this->language->get('error_api_key_access');
			$this->error['key'] = $this->language->get('error_api_key');
		}

		return !$this->error;
	}
}