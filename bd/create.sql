-- UTILISATEUR
CREATE TABLE utilisateur (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'formateur', 'etudiant') NOT NULL,
    statut ENUM('en_attente', 'valide', 'desactive') DEFAULT 'en_attente',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- COMPETENCE
CREATE TABLE competence (
    id_competence INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT
);

-- FORMATION
CREATE TABLE formation (
    id_formation INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    fichier_pdf VARCHAR(255),
    id_formateur INT,
    FOREIGN KEY (id_formateur) REFERENCES utilisateur(id_user)
);

-- FORMATION_COMPETENCE
CREATE TABLE formation_competence (
    id_formation INT,
    id_competence INT,
    PRIMARY KEY (id_formation, id_competence),
    FOREIGN KEY (id_formation) REFERENCES formation(id_formation) ON DELETE CASCADE,
    FOREIGN KEY (id_competence) REFERENCES competence(id_competence) ON DELETE CASCADE
);

-- QCM
CREATE TABLE qcm (
    id_qcm INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    id_formateur INT,
    FOREIGN KEY (id_formateur) REFERENCES utilisateur(id_user)
);

-- QCM_COMPETENCE
CREATE TABLE qcm_competence (
    id_qcm INT,
    id_competence INT,
    PRIMARY KEY (id_qcm, id_competence),
    FOREIGN KEY (id_qcm) REFERENCES qcm(id_qcm) ON DELETE CASCADE,
    FOREIGN KEY (id_competence) REFERENCES competence(id_competence) ON DELETE CASCADE
);

-- QUESTION
CREATE TABLE question (
    id_question INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    id_qcm INT,
    FOREIGN KEY (id_qcm) REFERENCES qcm(id_qcm) ON DELETE CASCADE
);

-- REPONSE
CREATE TABLE reponse (
    id_reponse INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    est_correcte BOOLEAN NOT NULL,
    id_question INT,
    FOREIGN KEY (id_question) REFERENCES question(id_question) ON DELETE CASCADE
);

-- TENTATIVE_QCM
CREATE TABLE tentative_qcm (
    id_tentative INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    id_qcm INT,
    score INT CHECK (score >= 0 AND score <= 100),
    date_passage DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES utilisateur(id_user),
    FOREIGN KEY (id_qcm) REFERENCES qcm(id_qcm)
);

-- PROJET
CREATE TABLE projet (
    id_projet INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255),
    description TEXT,
    id_competence INT,
    FOREIGN KEY (id_competence) REFERENCES competence(id_competence)
);

-- SOUMISSION_PROJET
CREATE TABLE soumission_projet (
    id_soumission INT AUTO_INCREMENT PRIMARY KEY,
    id_projet INT,
    id_user INT,
    fichier VARCHAR(255),
    date_soumission DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'valide', 'refuse') DEFAULT 'en_attente',
    commentaire_formateur TEXT,
    FOREIGN KEY (id_projet) REFERENCES projet(id_projet),
    FOREIGN KEY (id_user) REFERENCES utilisateur(id_user)
);

-- CRITERE_VALIDATION
CREATE TABLE critere_validation (
    id_critere INT AUTO_INCREMENT PRIMARY KEY,
    id_competence INT UNIQUE,
    score_min_qcm INT,
    projet_obligatoire BOOLEAN,
    description TEXT,
    FOREIGN KEY (id_competence) REFERENCES competence(id_competence)
);

-- VALIDATION_COMPETENCE
CREATE TABLE validation_competence (
    id_validation INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    id_competence INT,
    date_validation DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_user, id_competence),
    FOREIGN KEY (id_user) REFERENCES utilisateur(id_user),
    FOREIGN KEY (id_competence) REFERENCES competence(id_competence)
);

-- MESSAGE
CREATE TABLE message (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    id_etudiant INT,
    id_formateur INT,
    contenu TEXT,
    reponse TEXT,
    date_message DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_etudiant) REFERENCES utilisateur(id_user),
    FOREIGN KEY (id_formateur) REFERENCES utilisateur(id_user)
);