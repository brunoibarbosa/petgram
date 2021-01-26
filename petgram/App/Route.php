<?php

namespace App;

use MF\Init\Bootstrap;

class Route extends Bootstrap {

	protected function initRoutes() {

		$routes['home'] = [
			'route' => '/',
			'controller' => 'AppController',
			'action' => 'home'
		];

		$routes['login'] = [
			'route' => '/login',
			'controller' => 'IndexController',
			'action' => 'login'
		];

		$routes['register'] = [
			'route' => '/register',
			'controller' => 'IndexController',
			'action' => 'register'
		];

		$routes['accountLogin'] = [
			'route' => '/account-login',
			'controller' => 'AuthController',
			'action' => 'accountLogin'
		];

		$routes['accountRegister'] = [
			'route' => '/account-register',
			'controller' => 'IndexController',
			'action' => 'accountRegister'
		];

		$routes['logout'] = [
			'route' => '/logout',
			'controller' => 'AuthController',
			'action' => 'logout'
		];

		$routes['profile'] = [
			'route' => '/profile',
			'controller' => 'AppController',
			'action' => 'profile'
		];

		$routes['edit'] = [
			'route' => '/edit',
			'controller' => 'AppController',
			'action' => 'edit'
		];

		$routes['notifications'] = [
			'route' => '/notifications',
			'controller' => 'AppController',
			'action' => 'notifications'
		];

		$routes['explore'] = [
			'route' => '/explore',
			'controller' => 'AppController',
			'action' => 'explore'
		];

		$routes['updateUser'] = array(
			'route' => '/update-user',
			'controller' => 'AppController',
			'action' => 'updateUser'
		);

		$routes['followUser'] = array(
			'route' => '/follow',
			'controller' => 'AppController',
			'action' => 'followUser'
		);

		$routes['unfollowUser'] = array(
			'route' => '/unfollow',
			'controller' => 'AppController',
			'action' => 'unfollowUser'
		);

		$routes['disableAccount'] = array(
			'route' => '/disable-account',
			'controller' => 'AppController',
			'action' => 'disableAccount'
		);

		$routes['reactivateAccount'] = array(
			'route' => '/reactivate-account',
			'controller' => 'AppController',
			'action' => 'reactivateAccount'
		);

		$routes['accountStatus'] = array(
			'route' => '/account-status',
			'controller' => 'AppController',
			'action' => 'accountStatus'
		);

		$routes['followers'] = array(
			'route' => '/followers',
			'controller' => 'AppController',
			'action' => 'followers'
		);

		$routes['following'] = array(
			'route' => '/following',
			'controller' => 'AppController',
			'action' => 'following'
		);

		$routes['changeProfilePicture'] = array(
			'route' => '/change-profile-picture',
			'controller' => 'AppController',
			'action' => 'changeProfilePicture'
		);

		$routes['removeProfilePicture'] = array(
			'route' => '/remove-profile-picture',
			'controller' => 'AppController',
			'action' => 'removeProfilePicture'
		);

		$routes['postPhotography'] = array(
			'route' => '/post-photography',
			'controller' => 'AppController',
			'action' => 'postPhotography'
		);

		$routes['deletePhotography'] = array(
			'route' => '/delete-photography',
			'controller' => 'AppController',
			'action' => 'deletePhotography'
		);

		$routes['search'] = array(
			'route' => '/search',
			'controller' => 'AppController',
			'action' => 'search'
		);

		$routes['photo'] = array(
			'route' => '/photo',
			'controller' => 'AppController',
			'action' => 'photo'
		);

		$routes['likePhoto'] = array(
			'route' => '/like-photo',
			'controller' => 'AppController',
			'action' => 'likePhoto'
		);

		$routes['unlikePhoto'] = array(
			'route' => '/unlike-photo',
			'controller' => 'AppController',
			'action' => 'unlikePhoto'
		);

		$routes['commentPhoto'] = array(
			'route' => '/comments',
			'controller' => 'AppController',
			'action' => 'comments'
		);

		$routes['comments'] = array(
			'route' => '/comment-photo',
			'controller' => 'AppController',
			'action' => 'commentPhoto'
		);

		$routes['deleteCommentPhoto'] = array(
			'route' => '/delete-comment',
			'controller' => 'AppController',
			'action' => 'deleteCommentPhoto'
		);

		$routes['savePhoto'] = array(
			'route' => '/save-photo',
			'controller' => 'AppController',
			'action' => 'savePhoto'
		);

		$routes['unsavePhoto'] = array(
			'route' => '/unsave-photo',
			'controller' => 'AppController',
			'action' => 'unsavePhoto'
		);

		$routes['likes'] = array(
			'route' => '/likes',
			'controller' => 'AppController',
			'action' => 'likes'
		);

		$this->setRoutes($routes);
	}

}

?>