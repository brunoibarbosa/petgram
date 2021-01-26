<?php
	namespace App\Controllers;

	use MF\Controller\Action;
	use MF\Model\Container;

	class AuthController extends Action {
		public function accountLogin() {
			$user = Container::getModel('PetUser');
			$user->__set('username', $_GET['entry']);
			$user->__set('email', $_GET['entry']);
			$user->__set('petword', $_GET['password']);

			$user->authentication();

			session_start();

			if($user->__get('id') != '' && $user->__get('username')) {
				$_SESSION['user']['id'] = $user->__get('id');
				$_SESSION['user']['username'] = $user->__get('username');
				$_SESSION['user']['email'] = $user->__get('email');
				$_SESSION['user']['active'] = $user->__get('active');
				
				$photo = Container::getModel('Photo');
				$photo->__set('id_petuser', $_SESSION['user']['id']);
				
				$picture = $photo->getProfilePicture();
				$_SESSION['user']['profile_picture'] = $picture ? $picture->filepath : './uploads/default-profile.jpg';

				if ($_SESSION['user']['active'] == 0) {
					header('Location: /account-status');
					die();
				}

				unset($_SESSION['errors']);
				header('Location: /');
			} else {
				$_SESSION['userForm']['entry'] = $_GET['entry'];
				$_SESSION['user'] = [];
				$_SESSION['errors'][] = 'Dado(s) incorretos.';
				header('Location: /login?error');
			}
		}

		public function logout() {
			session_start();
			session_destroy();
			header('Location: /login');
		}
	}