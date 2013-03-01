<?php

	require_once(TOOLKIT.'/class.datasource.php');
	require_once(EXTENSIONS.'/frontend_localisation/lib/class.FLang.php');

	Class datasourcedomain_language extends Datasource
	{

		public function about(){
			return array(
				'name' => 'Domain Language',
				'author' => array(
					'name' => 'Jonathan Mifsud',
					'email' => 'info@jonmifsud.com',
					'website' => 'http://jonmifsud.com'
				),
				'version' => '1.0',
				'release-date' => '2012-02-21',
				'description' => 'Domain Language Redirect Datasource, showing the domains attached to each language'
			);
		}

		public function allowEditorToParse(){
			return false;
		}

		public function execute(&$param_pool = NULL){
			$result = new XMLElement('domain-language');

			$redirects = Symphony::Configuration()->get('domain_language_redirect');
			unset($redirects['enabled']);

			foreach ($redirects as $key => $value) {
				$lang_xml = new XMLElement('item', $value);
				$lang_xml->setAttribute('lang', $key);

				if( $key === FLang::getLangCode() ){
					$lang_xml->setAttribute('current', 'yes');
				}

				$result->appendChild($lang_xml);
				
			}

			return $result;
		}
	}
