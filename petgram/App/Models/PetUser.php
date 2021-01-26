<?php
	namespace App\Models;
	use MF\Model\Model;

	class PetUser extends Model {
		private $id;
		private $username;
		private $petname;
		private $bio;
		private $email;
		private $petword;
		private $id_animal;
		private $active;

		public function __get($attr) {
			return $this->$attr;
		}

		public function __set($attr, $value) {
			$this->$attr = $value;
		}

		public function create() {
			$query = "INSERT INTO petuser(email, petword, username, petname, id_animal, active) VALUES(?, md5(?), ?, ?, ?, 1)";
			$stmt = $this->db->prepare($query);
			$stmt->execute([
				$this->__get('email'),
				$this->__get('petword'),
				$this->__get('username'),
				$this->__get('petname'),
				$this->__get('id_animal')
			]);
			return $this;
		}

		public function update() {
			$query = "UPDATE petuser SET email = ?, username = ?, petname = ?, bio = ?, id_animal = ? WHERE id_petuser = ?";
			$stmt = $this->db->prepare($query);
			$stmt->execute([
				$this->__get('email'),
				$this->__get('username'),
				$this->__get('petname'),
				$this->__get('bio'),
				$this->__get('id_animal'),
				$this->__get('id')
			]);
			return $this;
		}

		public function validate() {
			$this->errors = [];
			
			if(!filter_var($this->__get('email'), FILTER_VALIDATE_EMAIL)) {
				$this->errors[] = 'Email inválido.';
			}

			if(strlen($this->__get('petword')) < 6 || strlen($this->__get('petword')) > 32) {
				$this->errors[] = 'A senha deve ter no mínimo 6 caracteres e no máximo 32.';
			}

			if(strlen($this->__get('username')) < 3 || strlen($this->__get('username')) > 30) {
				$this->errors[] = 'O nome de usuário deve ter no mínimo 3 caracteres e no máximo 30.';
			}

			if(strlen($this->__get('petname')) < 3 || strlen($this->__get('petname')) > 30) {
				$this->errors[] = 'O nome do pet deve ter no mínimo 3 caracteres e no máximo 30.';
			}

			if(trim($this->__get('bio') != '') && strlen($this->__get('bio')) > 50) {
				$this->errors[] = 'A biografia deve ter no máximo 50 caracteres.';
			}

			if(!strlen($this->__get('id_animal')) > 0) {
				$this->errors[] = 'Insira uma espécie válida.';
			}

			return $this->errors;
		}

		public function authentication() {
			$query = "SELECT id_petuser, username, email, active FROM petuser WHERE username = ? AND petword = md5(?) OR email = ? AND petword = md5(?) OR id_petuser = ? AND petword = md5(?)";
			$stmt = $this->db->prepare($query);
			$stmt->execute([
				$this->__get('username'),
				$this->__get('petword'),
				$this->__get('email'),
				$this->__get('petword'),
				$this->__get('id'),
				$this->__get('petword')
			]);

			$user = $stmt->fetch(\PDO::FETCH_OBJ);

			if ($user) {
				$this->__set('id', $user->id_petuser);
				$this->__set('username', $user->username);
				$this->__set('email', $user->email);
				$this->__set('email', $user->email);
				$this->__set('active', $user->active);
			}

			return $user;
		}

		public function getUserByEmail() {
			$query = "SELECT username, petname FROM petuser WHERE email = ?";
			$stmt = $this->db->prepare($query);
			$stmt->execute([$this->__get('email')]);

			return $stmt->fetch(\PDO::FETCH_OBJ);
		}

		public function getUserByUsername() {
			$query = "SELECT id_petuser, email, username, petname, bio, id_animal, active FROM petuser WHERE username = ?";
			$stmt = $this->db->prepare($query);
			$stmt->execute([$this->__get('username')]);

			return $stmt->fetch(\PDO::FETCH_OBJ);
		}

		public function login() {
			$query = "SELECT id, username, petname, email FROM petuser WHERE email = ? AND senha = ?";
			$stmt = $this->db->prepare($query);
			$stmt->execute([
				$this->__get('id'),
				$this->__get('username'),
				$this->__get('petname'),
				$this->__get('email')
			]);

			$usuario = $stmt->fetch(\PDO::FETCH_OBJ);

			if ($usuario->id_petuser != '' && $usuario->username != '') {
				$this->__set('id', $usuario['id']);
				$this->__set('username', $usuario['username']);
			}

			return $this;
		}

		public function getUserById() {
			$query = "SELECT id_petuser, email, username, petname, bio, id_animal FROM petuser WHERE id_petuser = ?";
			$stmt = $this->db->prepare($query);
			$stmt->execute([$this->__get('id')]);

			return $stmt->fetch(\PDO::FETCH_OBJ);
		}

		public function disableAccount() {
			$query = "UPDATE petuser SET active = 0 WHERE id_petuser = ?";
			$stmt = $this->db->prepare($query);
			$stmt->execute([$this->__get('id')]);

			return $this;
		}

		public function reactivateAccount() {
			$query = "UPDATE petuser SET active = 1 WHERE id_petuser = ?";
			$stmt = $this->db->prepare($query);
			$stmt->execute([$this->__get('id')]);

			return $this;
		}

		public function followUser($id_user_follow) {
			$query = "INSERT INTO petuser_follower(id_petuser, id_follower) VALUES(?, ?)";
			$stmt = $this->db->prepare($query);
			$stmt->execute([
				$id_user_follow,
				$this->__get('id')
			]);

			return true;
		}

		public function unfollowUser($id_user_following) {
			$query = "DELETE FROM petuser_follower WHERE id_petuser = ? AND id_follower = ?";
			$stmt = $this->db->prepare($query);
			$stmt->execute([
				$id_user_following,
				$this->__get('id')
			]);
			
			return true;
		}

		public function getTotalPost() {
			$query = "SELECT count(*) AS total_post FROM photo WHERE id_petuser = ? AND profile_picture = 0";
			$stmt = $this->db->prepare($query);
			$stmt->execute([$this->__get('id')]);

			return $stmt->fetch(\PDO::FETCH_OBJ);
		}

		public function getTotalFollowing() {
			$query = "SELECT count(*) AS following FROM petuser_follower WHERE id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1) AND id_follower = ?";
			$stmt = $this->db->prepare($query);
			$stmt->execute([$this->__get('id')]);

			return $stmt->fetch(\PDO::FETCH_OBJ);
		}

		public function getTotalFollower() {
			$query = "SELECT count(*) AS follower FROM petuser_follower WHERE id_follower IN (SELECT id_petuser FROM petuser WHERE active = 1) AND id_petuser = ?";
			$stmt = $this->db->prepare($query);
			$stmt->execute([$this->__get('id')]);

			return $stmt->fetch(\PDO::FETCH_OBJ);
		}

		public function getFollowers($offset, $limit) {
			$id = $this->__get('id');

			$query = "SELECT id_follower FROM petuser_follower WHERE id_follower IN (SELECT id_petuser FROM petuser WHERE active = 1) AND id_petuser = :id LIMIT :offset, :limitreturn";
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
			$stmt->bindParam(':limitreturn', $limit, \PDO::PARAM_INT);
			$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
			$stmt->execute();

			return $stmt->fetchAll(\PDO::FETCH_OBJ);
		}

		public function getFollowing($offset, $limit) {
			$id = $this->__get('id');

			$query = "SELECT id_petuser FROM petuser_follower WHERE id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1) AND id_follower = :id LIMIT :offset, :limitreturn";
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
			$stmt->bindParam(':limitreturn', $limit, \PDO::PARAM_INT);
			$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
			$stmt->execute();

			return $stmt->fetchAll(\PDO::FETCH_OBJ);
		}

		public function getFollowedByUser($user_id) {
			$query = "SELECT id_follower AS followed FROM petuser_follower WHERE id_petuser = ? AND id_follower = ?";
			$stmt = $this->db->prepare($query);
			$stmt->execute([
				$this->__get('id'),
				$user_id
				]);

			return $stmt->fetch(\PDO::FETCH_OBJ);
		}

		public function searchUser($offset, $limit) {
			$username = "%{$this->__get('username')}%";
			$petname = "%{$this->__get('petname')}%";

			$query = "SELECT id_petuser, email, username, petname, bio, id_animal, active FROM petuser WHERE username LIKE :username AND active = 1 OR petname LIKE :petname AND active = 1 ORDER BY petname LIMIT :offset, :limitreturn";
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
			$stmt->bindParam(':limitreturn', $limit, \PDO::PARAM_INT);
			$stmt->bindParam(':username', $username, \PDO::PARAM_STR);
			$stmt->bindParam(':petname', $petname, \PDO::PARAM_STR);
			$stmt->execute();

			return $stmt->fetchAll(\PDO::FETCH_OBJ);
		}

		public function totalSearchUser() {
			$query = "SELECT count(id_petuser) AS total_result FROM petuser WHERE username LIKE ? AND active = 1 OR petname LIKE ? AND active = 1 ORDER BY petname";
			$stmt = $this->db->prepare($query);
			$stmt->execute([
				"%{$this->__get('username')}%",
				"%{$this->__get('petname')}%",
				]);

			return $stmt->fetch(\PDO::FETCH_OBJ);
		}

		public function getTotalNotifications() {
			$id = $this->__get('id');

			$query = 
			"SELECT SUM(total) AS total_notification FROM (
				SELECT count(id_user_follower) AS total
				FROM petuser_follower
				WHERE id_petuser = :id_user AND id_follower IN (SELECT id_petuser FROM petuser WHERE active = 1)
	
				UNION ALL
	
				SELECT count(pl.id_like) AS total
				FROM photo_like pl LEFT JOIN photo p ON (pl.id_photo = p.id_photo)
				WHERE p.id_petuser = :id_user AND pl.id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1)

				UNION ALL

				SELECT count(pc.id_comment) AS total
				FROM photo_comment pc LEFT JOIN photo p ON (pc.id_photo = p.id_photo)
				WHERE p.id_petuser = :id_user AND pc.id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1)

				UNION ALL

				SELECT count(ps.id_save) AS total
				FROM photo_saved ps LEFT JOIN photo p ON (ps.id_photo = p.id_photo)
				WHERE p.id_petuser = :id_user AND ps.id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1)
				) as result";
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':id_user', $id, \PDO::PARAM_INT);
			$stmt->execute();
	
			return $stmt->fetch(\PDO::FETCH_OBJ);
		}

		public function getNotifications($offset, $limit) {
			$id_petuser = $this->__get('id');
	
			$query = 
			"SELECT * FROM (
				SELECT 'seguiu' AS action, DATE_FORMAT(pf.register_date, '%d/%m/%Y às %Hh%i') AS register_date, p.id_petuser, p.username AS action_reference 
				FROM petuser_follower pf LEFT JOIN petuser p ON (pf.id_follower = p.id_petuser)
				WHERE pf.id_petuser = :id_user AND pf.id_follower IN (SELECT id_petuser FROM petuser WHERE active = 1)
	
				UNION ALL
	
				SELECT 'curtiu' AS action, DATE_FORMAT(pl.register_date, '%d/%m/%Y às %Hh%i') AS register_date, p.id_petuser, ph.photoname AS action_reference 
				FROM photo_like pl LEFT JOIN photo ph ON (pl.id_photo = ph.id_photo)
				INNER JOIN petuser p ON (pl.id_petuser = p.id_petuser)
				WHERE ph.id_petuser = :id_user AND pl.id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1) AND p.id_petuser != :id_user

				UNION ALL

				SELECT 'comentou' AS action, DATE_FORMAT(pc.register_date, '%d/%m/%Y às %Hh%i') AS register_date, p.id_petuser, ph.photoname AS action_reference 
				FROM photo_comment pc LEFT JOIN photo ph ON (pc.id_photo = ph.id_photo)
				INNER JOIN petuser p ON (pc.id_petuser = p.id_petuser)
				WHERE ph.id_petuser = :id_user AND pc.id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1) AND p.id_petuser != :id_user

				UNION ALL

				SELECT 'salvou' AS action, DATE_FORMAT(ps.register_date, '%d/%m/%Y às %Hh%i') AS register_date, p.id_petuser, ph.photoname AS action_reference
				FROM photo_saved ps LEFT JOIN photo ph ON (ps.id_photo = ph.id_photo)
				INNER JOIN petuser p ON (ps.id_petuser = p.id_petuser)
				WHERE ph.id_petuser = :id_user AND ps.id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1) AND p.id_petuser != :id_user

				) as result
			ORDER BY result.register_date DESC
			LIMIT :offset, :limitreturn";
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
			$stmt->bindParam(':limitreturn', $limit, \PDO::PARAM_INT);
			$stmt->bindParam(':id_user', $id_petuser, \PDO::PARAM_INT);
			$stmt->execute();
	
			return $stmt->fetchAll(\PDO::FETCH_OBJ);
		}
	}
?>