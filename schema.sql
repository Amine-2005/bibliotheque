CREATE DATABASE IF NOT EXISTS bibliotheque CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bibliotheque;

CREATE TABLE IF NOT EXISTS livre (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    auteur VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    categorie VARCHAR(50),
    quantite INT NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telephone VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS emprunt (
    id INT AUTO_INCREMENT PRIMARY KEY,
    livre_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    date_emprunt DATE NOT NULL,
    date_retour_prevue DATE NOT NULL,
    date_retour DATE NULL,
    FOREIGN KEY (livre_id) REFERENCES livre(id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id)
);

INSERT IGNORE INTO livre (titre, auteur, isbn, categorie, quantite) VALUES
('Le Petit Prince', 'Antoine de Saint-Exupéry', '9782070612758', 'Roman', 3),
('L\'Étranger', 'Albert Camus', '9782070360024', 'Roman', 2),
('Les Misérables', 'Victor Hugo', '9782253096344', 'Classique', 2),
('Clean Code', 'Robert C. Martin', '9780132350884', 'Informatique', 1);

INSERT IGNORE INTO utilisateur (nom, prenom, email, telephone) VALUES
('Ben Salah', 'Karim', 'karim@example.com', '20123456'),
('Trabelsi', 'Sana', 'sana@example.com', '98123456');
