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

			);
		}


		/**
		 * Display options on Preferences page.
		 *
		 * @param array $context
		 */
		public function addCustomPreferenceFieldsets(array $context){
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', __('Domain Language Redirect')));

			$div = new XMLElement('div', null, array('class' => 'two columns'));

			foreach (FLang::getLangs() as $key => $language) {
				$label = Widget::Label(__("{$language} domain"), null, 'column', DLR_HANDLE."_{$language}");
				$label->appendChild(Widget::Input('settings['.DLR_HANDLE."][{$language}]", Symphony::Configuration()->get($language, DLR_HANDLE)));
				$label->appendChild(new XMLElement('p', __('Domain for this language.'), array('class' => 'help')));
				$div->appendChild($label);
			}

			$checkbox = Widget::Input('settings['.DLR_HANDLE.'][enabled]', "yes", 'checkbox');
			if( Symphony::Configuration()->get('enabled', DLR_HANDLE) == "yes" ){
				$checkbox->setAttribute('checked', 'checked');
			}

			$group->appendChild($div);

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