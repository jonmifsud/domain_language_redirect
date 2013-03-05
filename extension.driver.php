<?php

	define_safe(DLR_HANDLE, 'domain_language_redirect');

	Class extension_domain_language_redirect extends Extension{
		
		public static $xml;
		
		public function getSubscribedDelegates(){
			return array(
			
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'addCustomPreferenceFieldsets'
				),

				array(
					'page' => '/system/preferences/',
					'delegate' => 'CustomActions',
					'callback' => 'dCustomActions'
				),

				array(
					'page' => '/system/preferences/',
					'delegate' => 'Save',
					'callback' => 'save'
				),

				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'initaliseAdminPageHead'
				),

			);
		}


		/**
		 * Display options on Preferences page.
		 *
		 * @param array $context
		 */
		public function addCustomPreferenceFieldsets(array $context){
			$main_lang = FLang::getMainLang();
			$all_langs = FLang::getAllLangs();

			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', __('Domain Language Redirect')));

			$wrapper = new XMLElement('div', null, array('class' => 'field-multilingual'));

			$container = new XMLElement('div', null, array('class' => 'container'));
			$i = new XMLElement('i', '', array('class' => "tab-element tab-{$main_lang}", 'data-lang_code'=> "{$main_lang}"));
			$wrapper->appendChild($i);

			/*------------------------------------------------------------------------------------------------*/
			/*  Tabs  */
			/*------------------------------------------------------------------------------------------------*/

			$ul = new XMLElement('ul', null, array('class' => 'tabs'));
			foreach( FLang::getLangs() as $key => $language ){
				$li = new XMLElement('li', $all_langs[$language], array('class' => $language));
				$lc === $main_lang ? $ul->prependChild($li) : $ul->appendChild($li);
			}

			$container->appendChild($ul);


			/*------------------------------------------------------------------------------------------------*/
			/*  Panels  */
			/*------------------------------------------------------------------------------------------------*/

			foreach( FLang::getLangs() as $key => $language){
				$div = new XMLElement('div', null, array('class' => 'tab-panel tab-'.$language));

				$span = new XMLElement('span', null, array('class' => 'frame'));

				$span->appendChild(Widget::Input('settings['.DLR_HANDLE."][{$language}]", Symphony::Configuration()->get($language, DLR_HANDLE)));
				$div->appendChild($span);
				$container->appendChild($div);
			}
			
			$wrapper->appendChild($container);

			$checkbox = Widget::Input('settings['.DLR_HANDLE.'][enabled]', "yes", 'checkbox');
			if( Symphony::Configuration()->get('enabled', DLR_HANDLE) == "yes" ){
				$checkbox->setAttribute('checked', 'checked');
			}

			$group->appendChild($wrapper);

			$label = Widget::Label($checkbox->generate().' '.__('Domain Redirect Enabled'));
			$label->appendChild(new XMLElement('p', __('Check this to enable domain redirects.'), array('class' => 'help')));
			$group->appendChild($label);

			$context['wrapper']->appendChild($group);
		}

		/**
		 * Save options from Preferences page
		 *
		 * @param array $context
		 *
		 * @return boolean
		 */
		public function save(array $context){

			$valid = true;

			/* Language codes */
			foreach (FLang::getLangs() as $language) {
				//todo check if domain is valid
				if (!$valid){
					$context['errors'][DLR_HANDLE][$language] = __('Please make sure this is a valid domain.');
					$valid = false;
				} else {
					if ( Symphony::Configuration()->get($language, DLR_HANDLE) != $context['settings'][DLR_HANDLE][$language]){
						Symphony::Configuration()->set($language, $context['settings'][DLR_HANDLE][$language], DLR_HANDLE);
						// we should update htaccess maybe here or set a marker for later
					}
					unset($context['settings'][DLR_HANDLE][$language]);
				}
			}

			/* remove redirects for languages that do not exist */
			$old_langs = Symphony::Configuration()->get(DLR_HANDLE);
			unset($old_langs['enabled']);
			$old_langs = array_keys($old_langs);
			$deleted_languages = array_diff($old_langs, FLang::getLangs());

			foreach ($deleted_languages as $language) {
				Symphony::Configuration()->remove($language, DLR_HANDLE);
			}

			//if this is enabled.
			if (!$valid){
				$context['errors'][DLR_HANDLE]['enabled'] = __('This should be yes / no checkbox.');
				$valid = false;
			} else {
				if ( Symphony::Configuration()->get('enabled', DLR_HANDLE) != $context['settings'][DLR_HANDLE]['enabled']) {
					Symphony::Configuration()->set('enabled', $context['settings'][DLR_HANDLE]['enabled'], DLR_HANDLE);
					//should add / remove the htaccess block
				}
				unset($context['settings'][DLR_HANDLE]['enabled']);
			}

			Symphony::Configuration()->write();

			return $valid;
		}

		/**
		 * Add headers to the page.
		 *
		 * @param $type
		 */
		static public function initaliseAdminPageHead($type){
			$page = Administration::instance()->Page;
// die;
			// if( $type === self::SETTING_HEADERS ){

				$page->addStylesheetToHead(URL.'/extensions/frontend_localisation/assets/frontend_localisation.multilingual_tabs.css', 'screen', null, false);
				$page->addScriptToHead(URL.'/extensions/frontend_localisation/assets/frontend_localisation.multilingual_tabs.js', null, false);
				$page->addScriptToHead(URL.'/extensions/frontend_localisation/assets/frontend_localisation.multilingual_tabs_init.js', null, false);
			// }
		}
				
		public function enable(){
			return $this->install();
		}

		public function disable(){
		}

		public function install(){
		}

		public function uninstall(){
		}

	}

?>