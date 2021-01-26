CREATE DATABASE petgram CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;

USE petgram;

CREATE TABLE animal (
  id_animal INT NOT NULL AUTO_INCREMENT,
  species VARCHAR(30) NOT NULL,
  PRIMARY KEY (id_animal)
);

CREATE TABLE petuser (
  id_petuser INT NOT NULL AUTO_INCREMENT,
  email VARCHAR(256) NOT NULL UNIQUE,
  petword VARCHAR(32) NOT NULL,
  username VARCHAR(30) NOT NULL UNIQUE,
  petname VARCHAR(30) NOT NULL,
  id_animal INT NOT NULL,
  bio VARCHAR(50),
  active TINYINT NOT NULL,
  PRIMARY KEY (id_petuser),
  FOREIGN KEY (id_animal) REFERENCES animal (id_animal)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;

CREATE TABLE petuser_follower (
  id_user_follower INT NOT NULL AUTO_INCREMENT,
  id_petuser INT NOT NULL,
  id_follower INT NOT NULL,
  register_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_user_follower),
  FOREIGN KEY (id_petuser) REFERENCES petuser (id_petuser) ON DELETE CASCADE,
  FOREIGN KEY (id_follower) REFERENCES petuser (id_petuser) ON DELETE CASCADE
);

CREATE TABLE photo (
  id_photo INT NOT NULL AUTO_INCREMENT,
	photoname VARCHAR(50) NOT NULL,
	filetype VARCHAR(5) NOT NULL,
	filepath VARCHAR(255) NOT NULL,
	id_petuser INT NOT NULL,
  profile_picture TINYINT NOT NULL,
  register_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id_photo),
  FOREIGN KEY (id_petuser) REFERENCES petuser (id_petuser) ON DELETE CASCADE
);

CREATE TABLE photo_saved (
  id_save INT NOT NULL AUTO_INCREMENT,
  id_photo INT NOT NULL,
  id_petuser INT NOT NULL,
  register_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_save),
  FOREIGN KEY (id_photo) REFERENCES photo (id_photo) ON DELETE CASCADE,
  FOREIGN KEY (id_petuser) REFERENCES petuser (id_petuser) ON DELETE CASCADE
);

CREATE TABLE photo_like (
  id_like INT NOT NULL AUTO_INCREMENT,
  id_photo INT NOT NULL,
  id_petuser INT NOT NULL,
  register_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_like),
  FOREIGN KEY (id_photo) REFERENCES photo (id_photo) ON DELETE CASCADE,
  FOREIGN KEY (id_petuser) REFERENCES petuser (id_petuser) ON DELETE CASCADE
);

CREATE TABLE photo_comment (
  id_comment INT NOT NULL AUTO_INCREMENT,
  comment VARCHAR(250) NOT NULL,
  id_photo INT NOT NULL,
  id_petuser INT NOT NULL,
  register_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_comment),
  FOREIGN KEY (id_photo) REFERENCES photo (id_photo) ON DELETE CASCADE,
  FOREIGN KEY (id_petuser) REFERENCES petuser (id_petuser) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;

INSERT INTO animal(species) 
VALUES
  ('Gato'),
  ('Cachorro'),
  ('Pássaro'),
  ('Tartaruga'),
  ('Peixe');

INSERT INTO petuser(email, petword, username, petname, bio, id_animal, active) 
VALUES
  ('sabrina@gmail.com', md5(123456), 'sabrina2020', 'Sabrina', 'Durmo o dia inteiro 😸', 1, 1),
  ('theo@gmail.com', md5(123456), 'theo123', 'Theodoro', NULL, 2, 1),
  ('sofia@gmail.com', md5(123456), 'sofia_cat', 'Sofia', 'Mãe da sabrina2020 😻', 1, 1);

INSERT INTO photo(photoname, filetype, filepath, id_petuser, profile_picture) 
VALUES
  ('60088CBCB825B_200120212104', 'jpg', 'uploads/60088CBCB825B_200120212104.jpg', 1, 1),
  ('FDSG5YTGS825B_200120212104', 'jpg', 'uploads/FDSG5YTGS825B_200120212104.jpg', 2, 1),
  ('60088CHR32WK9_200120212104', 'jpg', 'uploads/60088CHR32WK9_200120212104.jpg', 3, 1),
  ('600890537FABD_200120212119', 'jpg', 'uploads/600890537FABD_200120212119.jpg', 1, 0),
  ('103890567FABE_250120211115', 'jpg', 'uploads/103890567FABE_250120211115.jpg', 3, 0),
  ('1038RTWER45BE_250120211115', 'jpg', 'uploads/1038RTWER45BE_250120211115.jpg', 3, 0),
  ('10ASDFASD3ABE_250120211115', 'jpg', 'uploads/10ASDFASD3ABE_250120211115.jpg', 3, 0),
  ('10384213GSABE_250120211115', 'jpg', 'uploads/10384213GSABE_250120211115.jpg', 1, 0),
  ('10389054123BE_250120211115', 'jpg', 'uploads/10389054123BE_250120211115.jpg', 1, 0),
  ('151260567FABE_250120211115', 'jpg', 'uploads/151260567FABE_250120211115.jpg', 3, 0),
  ('1038ERFRF267F_250120211115', 'jpg', 'uploads/1038ERFRF267F_250120211115.jpg', 1, 0),
  ('103DFGF67654E_250120211115', 'jpg', 'uploads/103DFGF67654E_250120211115.jpg', 1, 0),
  ('12342389234BE_250120211115', 'jpg', 'uploads/12342389234BE_250120211115.jpg', 2, 0),
  ('10312456REWBE_250120211115', 'jpg', 'uploads/10312456REWBE_250120211115.jpg', 3, 0),
  ('1RWEG52347ABE_250120211115', 'jpg', 'uploads/1RWEG52347ABE_250120211115.jpg', 1, 0),
  ('10389BCQWE123_250120211115', 'jpg', 'uploads/10389BCQWE123_250120211115.jpg', 2, 0),
  ('103VEFRW7FABE_250120211115', 'jpg', 'uploads/103VEFRW7FABE_250120211115.jpg', 1, 0);

INSERT INTO petuser_follower(id_petuser, id_follower) 
VALUES 
  (1, 2),
  (1, 3),
  (2, 1),
  (2, 3),
  (3, 1);

INSERT INTO photo_like(id_photo, id_petuser) 
VALUES 
  (6, 1),
  (16, 1),
  (14, 1),
  (9, 3),
  (4, 3),
  (8, 3),
  (9, 2),
  (8, 2),
  (5, 2),
  (6, 2),
  (15, 3);

INSERT INTO photo_saved(id_photo, id_petuser) 
VALUES 
  (6, 2),
  (8, 3),
  (9, 3),
  (13, 1),
  (6, 1);

INSERT INTO photo_comment(id_photo, id_petuser, comment) 
VALUES 
  (6, 2, 'só no banho de sol'),
  (17, 2, 'alongamento 🤣'),
  (8, 3, 'que preguiçosa!'),
  (15, 3, 'não fecha nem o olho pra dormir'),
  (13, 1, 'fala de mim, mas só dorme também'),
  (6, 1, '😻😻');