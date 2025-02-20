<?php
class ModelExtensionModuleDtranslate extends Model {
    public function install() {
        $this->db->query("CREATE TABLE `" . DB_PREFIX . "dtranslate_language` (`dtranslate_language_id` int(11) NOT NULL AUTO_INCREMENT, `language` varchar(10) NOT NULL, `name` varchar(55) NOT NULL, `supports_formality` int(1) NOT NULL, PRIMARY KEY (`dtranslate_language_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }

    public function uninstall() {
        $this->db->query("DELETE FROM " . DB_PREFIX . "dtranslate_language");
    }
	
	public function getTranslateLang() {
		$languages = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "dtranslate_language`");
		
		foreach($query->rows as $language) {
			$query_store_language = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE code LIKE '" . $this->db->escape($language['language']) . "%'");
			
			if($query_store_language->num_rows) {
				$languages['store'][] = array(
					'name'	=> $language['name'],
					'code'	=> $language['language'],
				);
			} else {
				$languages['other'][] = array(
					'name'	=> $language['name'],
					'code'	=> $language['language'],
				);
			}
		}
		
		return $languages;
	}
	
	public function addDtranslateLanguage($data) {		
		$sql = array();
		
		foreach($data as $language) {
			$sql[] = "('" . $this->db->escape(mb_strtolower($language['language'])) . "', '" . $this->db->escape($language['name']) . "', '" . (int)$language['supports_formality'] . "')";
		}
		
		if($sql) {
			$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "dtranslate_language`");
			
			$this->db->query("INSERT INTO `" . DB_PREFIX . "dtranslate_language` (`language`, `name`, `supports_formality`) VALUES " . implode(', ', $sql));
		}
	}
}