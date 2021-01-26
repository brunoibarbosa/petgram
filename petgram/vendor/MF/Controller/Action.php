<?php
	namespace MF\Controller;

	abstract class Action {
		protected $view;

		public function __construct() {
			$this->view = new \stdClass();
		}

		protected function render($view, $layout = null) {
			$this->view->page = $view;
		
			if (file_exists("../petgram/App/Views/".$layout.".phtml")) {
				require_once "../petgram/App/Views/".$layout.".phtml";
			} else {
				$this->content();
			}
		}

		protected function renderError() {
			$this->view->titlePage = 'Oops...';
			require_once "../petgram/App/Views/error.phtml";
		}

		protected function content() {
			$classAtual = get_class($this);

			$classAtual = str_replace('App\\Controllers\\', '', $classAtual);

			$classAtual = strtolower(str_replace('Controller', '', $classAtual));

			require_once "../petgram/App/Views/".$classAtual."/".$this->view->page.".phtml";
		}
	}
?>