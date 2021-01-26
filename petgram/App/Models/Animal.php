<?php

namespace App\Models;

use MF\Model\Model;

class Animal extends Model {
	private $id;
	private $species;

	public function __get($attr) {
		return $this->$attr;
	}

	public function __set($attr, $value) {
		$this->$attr = $value;
		return $this;
	}

	public function getAll() {
		$query = 
			"SELECT 
				id_animal,
				species
			FROM
				animal";
		$stmt = $this->db->prepare($query);
		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}
}