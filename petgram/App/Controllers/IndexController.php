<?php

	namespace App\Controllers;

	use MF\Controller\Action;
	use MF\Model\Container;

	class IndexController extends Action {
		public function login() {
			session_start();

			if (!isset($_SESSION['userForm'])) {
				$this->view->userForm['entry'] = '';
			} else {
				$this->view->userForm = $_SESSION['userForm'];
			}

			$this->view->titlePage = 'Entrar';
			$this->render('login', 'layout1');
		}

		public function register() {
			session_start();

			if (!isset($_SESSION['userForm'])) {
				$this->view->userForm = [
					'username' => '',
					'email' => '',
					'petname' => '',
					'animal' => '',
				];
			} else {
				$this->view->userForm = $_SESSION['userForm'];
			}

			$animals = Container::getModel('Animal');
			$this->view->animals = $animals->getAll();

			$this->view->titlePage = 'Cadastrar';
			$this->render('register', 'layout1');
		}

		public function accountRegister() {
			$user = Container::getModel('PetUser');

			$user->__set('username', strtolower($_POST['username']));
			$user->__set('petname', $_POST['petname']);
			$user->__set('email', strtolower($_POST['email']));
			$user->__set('petword', $_POST['password']);
			$user->__set('id_animal', intval($_POST['animal']));

			$validate = $user->validate();
			$email = $user->getUserByEmail();
			$username = $user->getUserByUsername();

			$user->authentication();

			session_start();

			if(count($validate) == 0 && !$email && !$username) {
				$user->create();
				header("Location: /account-login?entry={$user->__get('username')}&password={$user->__get('petword')}");
			} else {
				$_SESSION['userForm'] = [
					'username' => $_POST['username'],
					'petname' => $_POST['petname'],
					'email' => $_POST['email'],
					'animal' => $_POST['animal']
				];
				
				$_SESSION['errors'] = $validate;
				if ($email) {
					$_SESSION['errors'][] = 'Esse email já existe.';
				}
				if ($username) {
					$_SESSION['errors'][] = 'Esse nome de usuário já existe.';
				}

				header('Location: /register');
			}
		}
	}