<?php
	namespace App\Controllers;

	use MF\Controller\Action;
	use MF\Model\Container;

	class AppController extends Action {
		public function home() {
			$this->checkAuthentication();

			$user = Container::getModel('PetUser');
			$user->__set('id', $_SESSION['user']['id']);
			
			$limit = 4;
			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$offset = ($page - 1) * $limit;

			$photo = Container::getModel('Photo');
			$photo->__set('id_petuser', $_SESSION['user']['id']);

			$this->view->currentPage = $page;
			$this->view->totalPages = ceil($photo->getTotalPhotographyFeed()->total_photos / $limit);

			if ($this->view->currentPage > $this->view->totalPages && $this->view->currentPage > 1) {
				$this->renderError();
				die();
			}

			$feed = $photo->getPhotographyFeed($offset, $limit);

			$profile_picture = Container::getModel('Photo');
			foreach ($feed as $key => $photo) {
				$profile_picture->__set('id_petuser', $photo->id_petuser);
				$picture = $profile_picture->getProfilePicture();
				$photo->picture = $picture ? $picture->filepath : './uploads/default-profile.jpg';
			}

			$photoModel = Container::getModel('Photo');
			foreach ($feed as $key => $photo) {
				$photoModel->__set('id', $photo->id_photo);

				$offsetPhoto = 0;
				$limitPhoto = $photoModel->getTotalLikes()->total_likes;

				$photo->likes = $photoModel->getLikes($offsetPhoto, $limitPhoto);
				$photo->liked_by_user = false;

				$photo->saved = $photoModel->getSaved();
				$photo->saved_by_user = false;

				foreach ($photo->likes as $key => $like) {
					if ($like->id_petuser == $_SESSION['user']['id']) {
						$photo->liked_by_user = true;
					}
				}

				$user = Container::getModel('PetUser');
				$photo->last_like = end($photo->likes);
				if ($photo->last_like) {
					$user->__set('id', $photo->last_like->id_petuser);
					$photo->last_like = $user->getUserById();
				}

				foreach ($photo->saved as $key => $save) {
					if ($save->id_petuser == $_SESSION['user']['id']) {
						$photo->saved_by_user  = true;
					}
				}

				$photo->total_comments = $photoModel->getTotalComments()->total_comments;

				$offset = $photo->total_comments < 2 ? 0 : $photo->total_comments - 2;
				$limit = 2;
				$photo->comments = $photoModel->getCommentsByOffset($offset, $limit);

				foreach ($photo->comments as $key => $comment) {
					$user->__set('id', $comment->user);
					$comment->user = $user->getUserById();
				}
			}


			$this->view->feed = $feed;

			$this->view->active = 'home';
			$this->view->style = 'home';
			$this->view->titlePage = 'Home';
			$this->render('home', 'layout3');
		}

		public function checkAuthentication() {
			session_start();

			if(!isset($_SESSION['user']['id']) || $_SESSION['user']['id'] == '' || !isset($_SESSION['user']['username']) || $_SESSION['user']['username'] == '') {
				$_SESSION['errors'][] = 'É necessário realizar o login.';
				header('Location: /login?error');
				die();
			}

			if (!isset($_SESSION['user']['active']) || $_SESSION['user']['active'] == 0) {
				header('Location: /account-status');
				die();
			}
		}

		public function profile() {
			session_start();

			if (!isset($_SESSION['user']) && !isset($_GET['user'])) {
				header('Location: /search');
				die();
			}

			$username = isset($_GET['user']) ? $_GET['user'] : $_SESSION['user']['username'];

			$user = Container::getModel('PetUser');
			$user->__set('username', $username);
			$pet = $user->getUserByUsername();

			if (!$pet || $pet->active == 0) {
				$this->renderError();
			} else {
				$user = Container::getModel('PetUser');
				$user->__set('id', $pet->id_petuser);

				$pet->total_post = $user->getTotalPost()->total_post;
				$pet->follower = $user->getTotalFollower()->follower;
				$pet->following = $user->getTotalFollowing()->following;

				$this->view->teste = $user->getTotalFollower();

				$photo = Container::getModel('Photo');
				$photo->__set('id_petuser', $pet->id_petuser);
				$picture = $photo->getProfilePicture();
				$pet->picture = $picture ? $picture->filepath : './uploads/default-profile.jpg';

				$pet->photos = $photo->getPhotography();
				$photoSaved = $photo->getSavedByUser();
				
				if ($photoSaved) {
				$photoSearch = Container::getModel('Photo');
					foreach ($photoSaved as $key => $saved) {
						$photoSearch->__set('id', $saved->id_photo);
						$pet->photosSaved[] = $photoSearch->getPhotographyById();
					}
				} else {
					$pet->photosSaved = [];
				}

				$pet->followed_by_user = isset($_SESSION['user']['id']) ? $user->getFollowedByUser($_SESSION['user']['id']) : null;

				$this->view->user = $pet;
				$this->view->active = 'profile';
				$this->view->style = "profile";
				$this->view->titlePage = "$pet->petname (@$pet->username)";
				$this->render('profile', 'layout3');
			}
		}

		public function edit() {
			$this->checkAuthentication();

			$user = Container::getModel('PetUser');
			$user->__set('id', $_SESSION['user']['id']);
			$this->view->user = $user->getUserById();

			$animals = Container::getModel('Animal');
			$this->view->animals = $animals->getAll();

			$this->view->active = 'profile';
			$this->view->style = 'edit';
			$this->view->titlePage = 'Editar Perfil';
			$this->render('edit', 'layout2');
		}

		public function search() {
			session_start();

			$this->view->searchParams = isset($_GET['user']) ? trim($_GET['user']) : '';

			$user = Container::getModel('PetUser');
			$user->__set('username', $this->view->searchParams);
			$user->__set('petname', $this->view->searchParams);

			$limit = 10;
			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$offset = ($page - 1) * $limit;

			$this->view->currentPage = $page;
			$this->view->totalPages = $this->view->searchParams != '' ? ceil($user->totalSearchUser()->total_result / $limit) : 0;

			if ($this->view->currentPage > $this->view->totalPages && $this->view->currentPage > 1) {
				$this->renderError();
				die();
			}

			$this->view->searchResult = $this->view->searchParams == '' ? new \stdClass() : $user->searchUser($offset, $limit);

			if ($this->view->searchResult) {
				foreach ($this->view->searchResult as $key => $user) {
					$userSearch = Container::getModel('PetUser');
					$userSearch->__set('id', $user->id_petuser);

					$photo = Container::getModel('Photo');
					$photo->__set('id_petuser', $user->id_petuser);

					$picture = $photo->getProfilePicture();
					$user->picture = $picture ? $picture->filepath : './uploads/default-profile.jpg';

					$user->followed_by_user = isset($_SESSION['user']['id']) ? $userSearch->getFollowedByUser($_SESSION['user']['id']) : null;
				}
			}

			$this->view->active = 'search';
			$this->view->style = 'search';
			$this->view->titlePage = 'Pesquisar';
			$this->render('search', 'layout2');
		}

		public function explore() {
			session_start();

			$photo = Container::getModel('Photo');

			$limit = 12;
			$this->view->randomPhotos = $photo->getRandomPhotography($limit);

			$this->view->active = 'explore';
			$this->view->style = 'explore';
			$this->view->titlePage = 'Explorar';
			$this->render('explore', 'layout2');
		}

		public function updateUser() {
			$this->checkAuthentication();

			$bio = 	trim($_POST['bio']) != '' ? $_POST['bio'] : null;

			$user = Container::getModel('PetUser');
			$user->__set('id', strtolower($_SESSION['user']['id']));
			$user->__set('username', strtolower($_POST['username']));
			$user->__set('petname', $_POST['petname']);
			$user->__set('email', strtolower($_POST['email']));
			$user->__set('bio', $bio);
			$user->__set('petword', intval($_POST['password']));
			$user->__set('id_animal', intval($_POST['animal']));
			$user->__set('active', intval($_POST['active']));

			$error = false;
			
			// Validação dos dados
			$validate = $user->validate();
			if (count($validate) > 0) {
				$_SESSION['errors'] = $validate;
				$error = true;
			}

			// Instancia novo PetUser para fazer as consultas
			$currentUser = Container::getModel('PetUser');
			$currentUser->__set('id', $_SESSION['user']['id']);
			$currentUser->__set('username', $_SESSION['user']['username']);
			$currentUser->__set('email', $_SESSION['user']['email']);

			
			// Verifica se o nome de usuário já está em uso
			if ($currentUser->__get('username') != $user->__get('username')) {
				$username = $user->getUserByUsername();
				if ($username) {
					$_SESSION['errors'][] = 'Este nome de usuário já está em uso.';
					$error = true;
				}
			}

			// Verifica se o email já está em uso
			if ($currentUser->__get('email') != $user->__get('email')) {
				$email = $user->getUserByEmail();
				if ($email) {
					$_SESSION['errors'][] = 'Este email já está vinculado à uma conta.';
					$error = true;
				}
			}

			// Verifica se a senha está correta
			if ($currentUser->__get('petword') != $user->__get('petword')) {
				$userCopy = clone $user;
				$userSearch = $userCopy->authentication();
				if (!$userSearch) {
					$_SESSION['errors'][] = 'Senha incorreta.';
					$error = true;
				}
			}

			// Se tiver algum erro, retorna para página de edição
			if ($error) {
				header('Location: /edit?error');
				die();
			}

			$user->update();
			$_SESSION['user']['username'] = $user->__get('username');
			$_SESSION['user']['email'] = $user->__get('email');
			header('Location: /edit');
			die();
		}

		public function followUser() {
			$this->checkAuthentication();

			$userFollow = Container::getModel('PetUser');
			$userFollow->__set('username', $_GET['user']);
			$userFollow = $userFollow->getUserByUsername();
			$idUserFollow = $userFollow->id_petuser;

			$user = Container::getModel('PetUser');
			$user->__set('id', $_SESSION['user']['id']);

			
			if ($user->followUser($idUserFollow)) {
				if (isset($_SERVER['HTTP_REFERER'])) {
					header("Location: {$_SERVER['HTTP_REFERER']}");
					die();
				} else {
					header('Location: /');
					die();
				}
			}
		}

		public function unfollowUser() {
			$this->checkAuthentication();

			$userFollowing = Container::getModel('PetUser');
			$userFollowing->__set('username', $_GET['user']);
			$userFollowing = $userFollowing->getUserByUsername();
			$idUserFollowing = $userFollowing->id_petuser;

			$user = Container::getModel('PetUser');
			$user->__set('id', $_SESSION['user']['id']);

			if ($user->unfollowUser($idUserFollowing)) {
				if (isset($_SERVER['HTTP_REFERER'])) {
					header("Location: {$_SERVER['HTTP_REFERER']}");
					die();
				} else {
					header('Location: /');
					die();
				}
			}
		}

		public function disableAccount() {
			$this->checkAuthentication();

			$user = Container::getModel('PetUser');
			$user->__set('id', $_SESSION['user']['id']);
			$user = $user->disableAccount();

			session_destroy();
			header('Location: /login');
			die();
		}

		public function reactivateAccount() {
			session_start();

			$user = Container::getModel('PetUser');
			$user->__set('id', $_SESSION['user']['id']);
			$user = $user->reactivateAccount();

			$_SESSION['user']['active'] = 1;

			header('Location: /');
			die();
		}

		public function accountStatus() {
			session_start();
			
			if (!isset($_SESSION['user']['id']) || $_SESSION['user']['id'] == '') {
				header('Location: /login');
				die();
			}

			if (isset($_SESSION['user']['active']) && $_SESSION['user']['active'] == 1) {
				header('Location: /');
				die();
			}

			$this->view->titlePage = 'Conta desativada';
			$this->render('account-status', 'layout1');
		}

		public function followers() {
			session_start();

			$username = isset($_GET['user']) ? $_GET['user'] : $_SESSION['user']['username'];
			
			$this->view->username = $username;

			$user = Container::getModel('PetUser');
			$user->__set('username', $username);
			$pet = $user->getUserByUsername();

			if (!$pet) {
				$this->renderError();
			} else {
				$user = Container::getModel('PetUser');
				$user->__set('id', $pet->id_petuser);

				$limit = 10;
				$page = isset($_GET['page']) ? $_GET['page'] : 1;
				$offset = ($page - 1) * $limit;

				$this->view->currentPage = $page;
				$this->view->totalPages = ceil($user->getTotalFollower()->follower / $limit);

				if ($this->view->currentPage > $this->view->totalPages && $this->view->currentPage > 1) {
					$this->renderError();
					die();
				}

				$this->view->id_followers = $user->getFollowers($offset, $limit);
				$this->view->followers = [];

				foreach ($this->view->id_followers as $user => $id) {
					$user = Container::getModel('PetUser');
					$user->__set('id', $id->id_follower);

					$userSearch = $user->getUserById();

					$photo = Container::getModel('Photo');
					$photo->__set('id_petuser', $id->id_follower);

					$picture = $photo->getProfilePicture();
					$userSearch->picture = $picture ? $picture->filepath : './uploads/default-profile.jpg';

					$userSearch->followed_by_user = isset($_SESSION['user']['id']) ? $user->getFollowedByUser($_SESSION['user']['id']) : null;
					$this->view->followers[] = $userSearch;
				}

				$this->view->active = 'profile';
				$this->view->style = 'follow';
				$this->view->titlePage = "Seguidores (@$pet->username)";
				$this->render('followers', 'layout2');
			}
		}

		public function following() {
			session_start();

			$username = isset($_GET['user']) ? $_GET['user'] : $_SESSION['user']['username'];
			$this->view->username = $username;

			$user = Container::getModel('PetUser');
			$user->__set('username', $username);
			$pet = $user->getUserByUsername();

			if (!$pet) {
				$this->renderError();
			} else {
				$user = Container::getModel('PetUser');
				$user->__set('id', $pet->id_petuser);

				$limit = 10;
				$page = isset($_GET['page']) ? $_GET['page'] : 1;
				$offset = ($page - 1) * $limit;

				$this->view->currentPage = $page;
				$this->view->totalPages = ceil($user->getTotalFollowing()->following / $limit);

				if ($this->view->currentPage > $this->view->totalPages && $this->view->currentPage > 1) {
					$this->renderError();
					die();
				}

				$this->view->id_following = $user->getFollowing($offset, $limit);
				$this->view->following = [];

				foreach ($this->view->id_following as $user => $id) {
					$user = Container::getModel('PetUser');
					$user->__set('id', $id->id_petuser);
					$userInfo = $user->getUserById();

					$photo = Container::getModel('Photo');
					$photo->__set('id_petuser', $id->id_petuser);

					$picture = $photo->getProfilePicture();
					$userInfo->picture = $picture ? $picture->filepath : './uploads/default-profile.jpg';

					$this->view->following[] = $userInfo;
				}

				$this->view->active = 'profile';
				$this->view->style = 'follow';
				$this->view->titlePage = "Seguindo (@$pet->username)";
				$this->render('following', 'layout2');
			}
		}

		public function removeProfilePicture() {
			$this->checkAuthentication();

			$photo = Container::getModel('Photo');
			$photo->__set('id_petuser', $_SESSION['user']['id']);
			$photo->removeProfilePicture();

			$picture = $photo->getProfilePicture();
			$_SESSION['user']['profile_picture'] = $picture ? $picture->filepath : './uploads/default-profile.jpg';

			header('Location: /edit');
			die();
		}

		public function changeProfilePicture() {
			$this->checkAuthentication();

			$photo = Container::getModel('Photo');
			$photo->__set('file', $_FILES['file']);
			$photo->__set('id_petuser', $_SESSION['user']['id']);

			$errors = $photo->validate()->errors;

			if (count($errors) > 0) {
				foreach ($errors as $key => $error) {
					$_SESSION['errors'][] = $error;
				}

				header('Location: /edit?error');
				die();
			}

			$photo->removeProfilePicture();
			$result = $photo->changeProfilePicture();

			$picture = $photo->getProfilePicture();
			$_SESSION['user']['profile_picture'] = $picture ? $picture->filepath : './uploads/default-profile.jpg';

			header('Location: /edit');
			die();
		}

		public function postPhotography() {
			$this->checkAuthentication();

			$photo = Container::getModel('Photo');
			$photo->__set('file', $_FILES['file']);
			$photo->__set('id_petuser', $_SESSION['user']['id']);

			$errors = $photo->validate()->errors;

			if (count($errors) > 0) {
				foreach ($errors as $key => $error) {
					$_SESSION['errors'][] = $error;
				}

				header('Location: /edit?error');
				die();
			}

			$photo->postPhotography();

			header('Location: /profile');
			die();
		}

		public function deletePhotography() {
			$this->checkAuthentication();

			$photoSearch = Container::getModel('Photo');
			$photoSearch->__set('photoname', $_GET['p']);
			$photoSearch = $photoSearch->getPhotographyByName();

			$photo = Container::getModel('Photo');
			$photo->__set('id', $photoSearch->id_photo);
			$photo->__set('id_petuser', $_SESSION['user']['id']);
			$photo->__set('filepath', $photoSearch->filepath);

			if ($photo->deletePhotography()) {
				header('Location: /profile');
				die();
			}
		}

		public function photo() {
			$this->checkAuthentication();

			$photo = Container::getModel('Photo');
			$photo->__set('photoname', $_GET['p']);
			$photoSearch = $photo->getPhotographyByName();

			if (!$photoSearch) {
				$this->renderError();
			} else {
				$photo->__set('id', $photoSearch->id_photo);

				$offsetPhoto = 0;
				$limitPhoto = $photo->getTotalLikes()->total_likes;

				$photoSearch->likes = $photo->getLikes($offsetPhoto, $limitPhoto);
				$photoSearch->liked_by_user = false;
				foreach ($photoSearch->likes as $key => $like) {
					if ($like->id_petuser == $_SESSION['user']['id']) {
						$photoSearch->liked_by_user = true;
					}
				}

				$user = Container::getModel('PetUser');
				$photoSearch->last_like = end($photoSearch->likes);
				if ($photoSearch->last_like) {
					$user->__set('id', $photoSearch->last_like->id_petuser);
					$photoSearch->last_like = $user->getUserById();
				}

				$photoSearch->saved = $photo->getSaved();
				$photoSearch->saved_by_user = false;
				foreach ($photoSearch->saved as $key => $save) {
					if ($save->id_petuser == $_SESSION['user']['id']) {
						$photoSearch->saved_by_user  = true;
					}
				}

				$photoSearch->total_comments = $photo->getTotalComments()->total_comments;

				$offset = $photoSearch->total_comments < 2 ? 0 : $photoSearch->total_comments - 2;
				$limit = 2;

				$photoSearch->comments = $photo->getCommentsByOffset($offset, $limit);
				$profile_picture = Container::getModel('Photo');

				foreach ($photoSearch->comments as $key => $comment) {
					$user->__set('id', $comment->user);
					$profile_picture->__set('id_petuser', $comment->user);

					$comment->user = $user->getUserById();
					$picture = $profile_picture->getProfilePicture();
					$comment->user->picture = $picture ? $picture->filepath : './uploads/default-profile.jpg';
				}

				$this->view->photo = $photoSearch;

				$user = Container::getModel('PetUser');
				$user->__set('id', $this->view->photo->id_petuser);
				$userSearch = $user->getUserById();

				$profile_picture = Container::getModel('Photo');
				$profile_picture->__set('id_petuser', $userSearch->id_petuser);
				$picture = $profile_picture->getProfilePicture();
				$userSearch->picture = $picture ? $picture->filepath : './uploads/default-profile.jpg';

				$this->view->user = $userSearch;

				$this->view->active = 'profile';
				$this->view->style = 'photo';
				$this->view->titlePage = "Foto de {$userSearch->petname}";
				$this->render('photo', 'layout2');
			}
		}

		public function likePhoto() {
			$this->checkAuthentication();

			$photoSearch = Container::getModel('Photo');
			$photoSearch->__set('photoname', $_GET['p']);
			$photoSearch = $photoSearch->getPhotographyByName();

			$photo = Container::getModel('Photo');
			$photo->__set('id', $photoSearch->id_photo);
			$photo->__set('id_petuser', $_SESSION['user']['id']);

			if ($photo->likePhoto()) {
				if (isset($_SERVER['HTTP_REFERER'])) {
					header("Location: {$_SERVER['HTTP_REFERER']}");
					die();
				} else {
					header('Location: /');
					die();
				}
			}
		}

		public function likes() {
			$this->checkAuthentication();

			$photo = Container::getModel('Photo');
			$photo->__set('photoname', $_GET['p']);
			$photoSearch = $photo->getPhotographyByName();
			$photo->__set('id', $photoSearch->id_photo);

			$limit = 10;
			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$offset = ($page - 1) * $limit;

			$this->view->currentPage = $page;
			$this->view->totalPages = ceil($photo->getTotalLikes()->total_likes / $limit);

			if ($this->view->currentPage > $this->view->totalPages && $this->view->currentPage > 1) {
				$this->renderError();
				die();
			}

			if (!$photoSearch) {
				$this->renderError();
			} else {
				$photo->__set('id', $photoSearch->id_photo);
				$likes = $photo->getLikes($offset, $limit);

				$profile_picture = Container::getModel('Photo');
				$user = Container::getModel('PetUser');
				foreach ($likes as $key => $like) {
					$user->__set('id', $like->id_petuser);
					$profile_picture->__set('id_petuser', $like->id_petuser);

					$like->user = $user->getUserById();
					$picture = $profile_picture->getProfilePicture();
					$like->user->picture = $picture ? $picture->filepath : './uploads/default-profile.jpg';

					$like->followed_by_user = isset($_SESSION['user']['id']) ? $user->getFollowedByUser($_SESSION['user']['id']) : null;
					$this->view->likes[] = $like;
				}

				$this->view->active = 'profile';
				$this->view->style = 'follow';
				$this->view->titlePage = "Curtidas";
				$this->render('likes', 'layout2');
			}																		
		}

		public function unlikePhoto() {
			$this->checkAuthentication();

			$photoSearch = Container::getModel('Photo');
			$photoSearch->__set('photoname', $_GET['p']);
			$photoSearch = $photoSearch->getPhotographyByName();

			$photo = Container::getModel('Photo');
			$photo->__set('id', $photoSearch->id_photo);
			$photo->__set('id_petuser', $_SESSION['user']['id']);

			if ($photo->unlikePhoto()) {
				if (isset($_SERVER['HTTP_REFERER'])) {
					header("Location: {$_SERVER['HTTP_REFERER']}");
					die();
				} else {
					header('Location: /');
					die();
				}
			}
		}
		
		public function commentPhoto() {
			$this->checkAuthentication();

			$photoSearch = Container::getModel('Photo');
			$photoSearch->__set('photoname', $_POST['photoname']);
			$photoSearch = $photoSearch->getPhotographyByName();

			$photo = Container::getModel('Photo');
			$photo->__set('id', $photoSearch->id_photo);
			$photo->__set('id_petuser', $_SESSION['user']['id']);
			$photo->__set('comment', trim($_POST['comment']));

			if ($photo->__get('comment') != '' && $photo->commentPhoto()) {
				if (isset($_SERVER['HTTP_REFERER'])) {
					header("Location: {$_SERVER['HTTP_REFERER']}");
					die();
				} else {
					header('Location: /');
					die();
				}
			} else {
				header("Location: {$_SERVER['HTTP_REFERER']}&error#commentInput");
				$_SESSION['errors'][] = 'O comentário não pode ser vazio.';
				die();
			}
		}
		
		public function comments() {
			$this->checkAuthentication();

			$photo = Container::getModel('Photo');
			$photo->__set('photoname', $_GET['p']);
			$photoSearch = $photo->getPhotographyByName();
			$photo->__set('id', $photoSearch->id_photo);

			$limit = 8;
			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$offset = ($page - 1) * $limit;

			$this->view->currentPage = $page;
			$this->view->totalPages = ceil($photo->getTotalComments()->total_comments / $limit);

			if ($this->view->currentPage > $this->view->totalPages && $this->view->currentPage > 1) {
				$this->renderError();
				die();
			} 

			if (!$photoSearch) {
				$this->renderError();
			} else {
				$photo->__set('id', $photoSearch->id_photo);
				$likes = $photo->getLikes($offset, $limit);
				
				$user = Container::getModel('PetUser');
				$photoSearch->comments = $photo->getCommentsByOffset($offset, $limit);
				$profile_picture = Container::getModel('Photo');

				foreach ($photoSearch->comments as $key => $comment) {
					$user->__set('id', $comment->user);
					$profile_picture->__set('id_petuser', $comment->user);

					$comment->user = $user->getUserById();
					$picture = $profile_picture->getProfilePicture();
					$comment->user->picture = $picture ? $picture->filepath : './uploads/default-profile.jpg';
				}

				$this->view->photo = $photoSearch;

				$this->view->active = 'profile';
				$this->view->style = 'follow';
				$this->view->titlePage = "Comentários";
				$this->render('comments', 'layout2');
			}																		
		}

		public function deleteCommentPhoto() {
			$this->checkAuthentication();

			$photo = Container::getModel('Photo');
			$photo->__set('id_petuser', $_SESSION['user']['id']);

			if ($photo->deleteComment($_GET['comment'])) {
				if (isset($_SERVER['HTTP_REFERER'])) {
					header("Location: {$_SERVER['HTTP_REFERER']}");
					die();
				} else {
					header('Location: /');
					die();
				}
			}
		}

		public function savePhoto() {
			$this->checkAuthentication();

			$photoSearch = Container::getModel('Photo');
			$photoSearch->__set('photoname', $_GET['p']);
			$photoSearch = $photoSearch->getPhotographyByName();

			$photo = Container::getModel('Photo');
			$photo->__set('id', $photoSearch->id_photo);
			$photo->__set('id_petuser', $_SESSION['user']['id']);

			if ($photo->savePhoto()) {
				if (isset($_SERVER['HTTP_REFERER'])) {
					header("Location: {$_SERVER['HTTP_REFERER']}");
					die();
				} else {
					header('Location: /');
					die();
				}
			}
		}

		public function unsavePhoto() {
			$this->checkAuthentication();

			$photoSearch = Container::getModel('Photo');
			$photoSearch->__set('photoname', $_GET['p']);
			$photoSearch = $photoSearch->getPhotographyByName();

			$photo = Container::getModel('Photo');
			$photo->__set('id', $photoSearch->id_photo);
			$photo->__set('id_petuser', $_SESSION['user']['id']);

			if ($photo->unsavePhoto()) {
				if (isset($_SERVER['HTTP_REFERER'])) {
					header("Location: {$_SERVER['HTTP_REFERER']}");
					die();
				} else {
					header('Location: /');
					die();
				}
			}
		}

		public function notifications() {
			$this->checkAuthentication();

			$user = Container::getModel('PetUser');
			$user->__set('id', $_SESSION['user']['id']);

			$limit = 10;
			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$offset = ($page - 1) * $limit;

			$this->view->currentPage = $page;
			$this->view->totalPages = ceil($user->getTotalNotifications()->total_notification / $limit);

			if ($this->view->currentPage > $this->view->totalPages && $this->view->currentPage > 1) {
				$this->renderError();
				die();
			}

			$this->view->notifications = $user->getNotifications($offset, $limit);

			$userSearch = Container::getModel('PetUser');
			$photoSearch = Container::getModel('Photo');
			foreach ($this->view->notifications as $key => $notification) {
				$userSearch->__set('id', $notification->id_petuser);
				$photoSearch->__set('id_petuser', $notification->id_petuser);
				$notification->user = $userSearch->getUserById();
				$notification->user->picture = $photoSearch->getProfilePicture()->filepath;

				switch ($notification->action) {
					case 'seguiu':
						$notification->content = 'seguiu você';
						$notification->hrefButton = "/profile?user={$notification->action_reference}";
						break;
					case 'curtiu':
						$notification->content = 'curtiu sua foto';
						$notification->hrefButton = "/photo?p={$notification->action_reference}";
						break;
					case 'comentou':
						$notification->content = 'comentou na sua foto';
						$notification->hrefButton = "/photo?p={$notification->action_reference}";
						break;
					case 'salvou':
						$notification->content = 'salvou sua foto';
						$notification->hrefButton = "/photo?p={$notification->action_reference}";
						break;
				}
			}

			$this->view->active = 'notification';
			$this->view->style = 'follow';
			$this->view->titlePage = "Notificações";
			$this->render('notifications', 'layout2');																	
		}
	}