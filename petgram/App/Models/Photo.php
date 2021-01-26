<?php

namespace App\Models;

use MF\Model\Model;

class Photo extends Model {
	private $id;
	private $photoname;
	private $filetype;
	private $filepath;
	private $id_petuser;
	private $profile_picture;
	private $file;
	public $errors = [];

	public function __get($attr) {
		return $this->$attr;
	}

	public function __set($attr, $value) {
		$this->$attr = $value;
		return $this;
	}

	private function uploadFile() {
		move_uploaded_file($this->__get('file')['tmp_name'], "uploads/{$this->__get('photoname')}.{$this->__get('filetype')}");
	}

	public function validate() {
		if ($this->__get('file')['size'] / 1024 > 2048 ) {
			$this->errors[] = 'A imagem deve ter menos de 2MB.';
		}

		return $this;
	}

	private function generateInfo() {
		$this->__set('photoname', strtoupper(uniqid()).'_'.date('dmYHi'));

		$explode_file = explode('.', $_FILES['file']['name']);
		$this->__set('filetype', $explode_file[count($explode_file) - 1]);

		$this->__set('filepath', "uploads/{$this->__get('photoname')}.{$this->__get('filetype')}");
	}

	public function changeProfilePicture() {
		$this->validate();

		if (count($this->errors) > 0) {
			return this;
		}

		$this->generateInfo();

		if (count($this->errors) == 0) {
			$query = "INSERT INTO photo(photoname, filetype, filepath, id_petuser, profile_picture) VALUES(?, ?, ?, ?, 1)";
			$stmt = $this->db->prepare($query);
			$stmt->execute([
				$this->__get('photoname'),
				$this->__get('filetype'),
				$this->__get('filepath'),
				$this->__get('id_petuser'),
			]);

			$this->uploadFile();
		}
		return $this;
	}

	public function getProfilePicture() {
		$query = "SELECT filepath FROM photo WHERE id_petuser = ? AND profile_picture = 1";
		$stmt = $this->db->prepare($query);
		$stmt->execute([$this->__get('id_petuser')]);

		return $stmt->fetch(\PDO::FETCH_OBJ);
	}

	public function removeProfilePicture() {
		$photo = $this->getProfilePicture();
		unlink($photo->filepath);

		$query = "DELETE FROM photo WHERE id_petuser = ? AND profile_picture = 1";
		$stmt = $this->db->prepare($query);
		$stmt->execute([$this->__get('id_petuser')]);

		return true;
	}

	public function deletePhotography() {
		unlink($this->__get('filepath'));

		$query = "DELETE FROM photo WHERE id_petuser = ? AND filepath = ?";
		$stmt = $this->db->prepare($query);
		$stmt->execute([
			$this->__get('id_petuser'),
			$this->__get('filepath')
			]);

		return true;
	}

	public function postPhotography() {
		$this->generateInfo();

		$query = "INSERT INTO photo(photoname, filetype, filepath, id_petuser, profile_picture) VALUES(?, ?, ?, ?, 0)";
		$stmt = $this->db->prepare($query);
		$stmt->execute([
			$this->__get('photoname'),
			$this->__get('filetype'),
			$this->__get('filepath'),
			$this->__get('id_petuser'),
		]);

		$this->uploadFile();
		return $this;
	}

	public function getPhotography() {
		$query = "SELECT id_photo, photoname, filepath, register_date FROM photo WHERE id_petuser = ? AND profile_picture = 0 ORDER BY register_date DESC";
		$stmt = $this->db->prepare($query);
		$stmt->execute([$this->__get('id_petuser')]);

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	public function getPhotographyById() {
		$query = "SELECT id_photo, filepath, photoname, DATE_FORMAT(register_date, '%d/%m/%Y') as register_date, id_petuser FROM photo WHERE id_photo = ? AND profile_picture = 0 AND id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1)";
		$stmt = $this->db->prepare($query);
		$stmt->execute([$this->__get('id')]);

		return $stmt->fetch(\PDO::FETCH_OBJ);
	}

	public function getPhotographyByName() {
		$query = "SELECT id_photo, filepath, photoname, DATE_FORMAT(register_date, '%d/%m/%Y') as register_date, id_petuser FROM photo WHERE photoname = ? AND profile_picture = 0 AND id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1)";
		$stmt = $this->db->prepare($query);
		$stmt->execute([$this->__get('photoname')]);

		return $stmt->fetch(\PDO::FETCH_OBJ);
	}

	public function likePhoto() {
		$query = "INSERT INTO photo_like(id_photo, id_petuser) VALUES(?, ?)";
		$stmt = $this->db->prepare($query);
		$stmt->execute([
			$this->__get('id'),
			$this->__get('id_petuser')
		]);

		return true;
	}

	public function unlikePhoto() {
		$query = "DELETE FROM photo_like WHERE id_photo = ? AND id_petuser = ?";
		$stmt = $this->db->prepare($query);
		$stmt->execute([
			$this->__get('id'),
			$this->__get('id_petuser')
		]);

		return true;
	}

	public function getLikes($offset, $limit) {
		$id = $this->__get('id');

		$query = "SELECT id_petuser FROM photo_like WHERE id_photo = :id AND id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1) ORDER BY register_date LIMIT :offset, :limitreturn";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
		$stmt->bindParam(':limitreturn', $limit, \PDO::PARAM_INT);
		$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	public function getTotalLikes() {
		$query = "SELECT count(id_petuser) AS total_likes FROM photo_like WHERE id_photo = ? AND id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1) ORDER BY register_date";
		$stmt = $this->db->prepare($query);
		$stmt->execute([$this->__get('id')]);

		return $stmt->fetch(\PDO::FETCH_OBJ);
	}
	
	public function commentPhoto() {
		$query = "INSERT INTO photo_comment(comment, id_photo, id_petuser) VALUES(?, ?, ?)";
		$stmt = $this->db->prepare($query);
		$stmt->execute([
			$this->__get('comment'),
			$this->__get('id'),
			$this->__get('id_petuser')
		]);

		return true;
	}

	public function getComments() {
		$query = "SELECT id_comment, comment, id_petuser AS user, DATE_FORMAT(register_date, '%d/%m/%Y às %Hh%i') AS register_date FROM photo_comment WHERE id_photo = ? AND id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1)";
		$stmt = $this->db->prepare($query);
		$stmt->execute([$this->__get('id')]);

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	public function getCommentsByOffset($offset, $limit) {
		$id = $this->__get('id');

		$query = "SELECT id_comment, comment, id_petuser AS user, DATE_FORMAT(register_date, '%d/%m/%Y às %Hh%i') AS register_date 
		FROM photo_comment 
		WHERE id_photo = :id_photo AND id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1)
		LIMIT :offset, :limitreturn";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':id_photo', $id, \PDO::PARAM_INT);
		$stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
		$stmt->bindParam(':limitreturn', $limit, \PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	public function getTotalComments() {
		$query = "SELECT count(id_comment) AS total_comments FROM photo_comment WHERE id_photo = ? AND id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1)";
		$stmt = $this->db->prepare($query);
		$stmt->execute([$this->__get('id')]);

		return $stmt->fetch(\PDO::FETCH_OBJ);
	}

	public function deleteComment($id_comment) {
		$query = "DELETE FROM photo_comment WHERE id_comment = ? AND id_petuser = ?";
		$stmt = $this->db->prepare($query);
		$stmt->execute([
			$id_comment,
			$this->__get('id_petuser')
		]);

		return true;
	}

	public function savePhoto() {
		$query = "INSERT INTO photo_saved(id_photo, id_petuser) VALUES(?, ?)";
		$stmt = $this->db->prepare($query);
		$stmt->execute([
			$this->__get('id'),
			$this->__get('id_petuser')
		]);

		return true;
	}

	public function unsavePhoto() {
		$query = "DELETE FROM photo_saved WHERE id_photo = ? AND id_petuser = ?";
		$stmt = $this->db->prepare($query);
		$stmt->execute([
			$this->__get('id'),
			$this->__get('id_petuser')
		]);

		return true;
	}

	public function getSaved() {
		$query = "SELECT id_petuser FROM photo_saved WHERE id_photo = ? AND id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1)";
		$stmt = $this->db->prepare($query);
		$stmt->execute([$this->__get('id')]);

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	public function getSavedByUser() {
		$query = "SELECT id_photo FROM photo_saved WHERE id_petuser = ?";
		$stmt = $this->db->prepare($query);
		$stmt->execute([$this->__get('id_petuser')]);

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	public function getPhotographyFeed($offset, $limit) {
		$id_petuser = $this->__get('id_petuser');

		$query = 
		"SELECT * FROM (
			SELECT p.id_petuser, p.username, p.petname, ph.id_photo, ph.photoname, ph.filepath, DATE_FORMAT(ph.register_date, '%d/%m/%Y às %Hh%i') as register_date
				FROM petuser_follower pf INNER JOIN petuser p ON (pf.id_petuser = p.id_petuser)
				INNER JOIN photo ph ON (p.id_petuser = ph.id_petuser)
				WHERE pf.id_follower = :id_user AND profile_picture = 0 AND p.active = 1
					
			UNION
			
			SELECT p.id_petuser, p.username, p.petname, ph.id_photo, ph.photoname, ph.filepath, DATE_FORMAT(ph.register_date, '%d/%m/%Y às %Hh%i') as register_date
				FROM petuser p INNER JOIN photo ph ON (p.id_petuser = ph.id_petuser)
				WHERE p.id_petuser = :id_user AND profile_picture = 0 AND p.active = 1
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

	public function getTotalPhotographyFeed() {
		$id_petuser = $this->__get('id_petuser');

		$query = 
		"SELECT SUM(total_photos) AS total_photos FROM (
			SELECT count(ph.id_photo) AS total_photos
				FROM petuser_follower pf INNER JOIN petuser p ON (pf.id_petuser = p.id_petuser)
				INNER JOIN photo ph ON (p.id_petuser = ph.id_petuser)
				WHERE pf.id_follower = :id_user AND profile_picture = 0 AND p.active = 1

			UNION

			SELECT count(ph.id_photo) AS total_photos2
				FROM petuser p INNER JOIN photo ph ON (p.id_petuser = ph.id_petuser)
				WHERE p.id_petuser = :id_user AND profile_picture = 0 AND p.active = 1
			) as result";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':id_user', $id_petuser, \PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetch(\PDO::FETCH_OBJ);
	}

	public function getRandomPhotography($limit) {
		$query = "SELECT id_photo, photoname, filepath, register_date
		FROM photo 
		WHERE id_petuser IN (SELECT id_petuser FROM petuser WHERE active = 1) AND profile_picture = 0
		ORDER BY RAND()
		LIMIT :limitreturn";
		$stmt = $this->db->prepare($query);
		$stmt->bindParam(':limitreturn', $limit, \PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}
}