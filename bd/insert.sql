-- UTILISATEUR
INSERT INTO utilisateur (nom, email, password_hash, role, statut)
VALUES
('Admin System', 'admin@mail.com', 'hash1', 'admin', 'valide'),
('Jean Formateur', 'formateur@mail.com', 'hash2', 'formateur', 'valide'),
('Alice Etudiant', 'alice@mail.com', 'hash3', 'etudiant', 'valide'),
('Bob Etudiant', 'bob@mail.com', 'hash4', 'etudiant', 'en_attente');

-- COMPETENCE
INSERT INTO competence (nom, description)
VALUES
('Réseaux', 'Bases des réseaux'),
('Sécurité Web', 'Protection des applications web'),
('Cryptographie', 'Chiffrement et sécurité des données');

-- FORMATION
INSERT INTO formation (titre, fichier_pdf, id_formateur)
VALUES
('Intro Réseaux', 'reseaux.pdf', 2),
('Sécurité Web Avancée', 'web.pdf', 2);

-- FORMATION_COMPETENCE
INSERT INTO formation_competence VALUES
(1,1),
(2,2);

-- QCM
INSERT INTO qcm (titre, id_formateur)
VALUES
('QCM Réseaux', 2),
('QCM Web', 2);

-- QCM_COMPETENCE
INSERT INTO qcm_competence VALUES
(1,1),
(2,2);

-- QUESTION
INSERT INTO question (contenu, id_qcm)
VALUES
('Qu est-ce qu une adresse IP ?', 1),
('Qu est-ce que HTTPS ?', 2);

-- REPONSE
INSERT INTO reponse (contenu, est_correcte, id_question)
VALUES
('Identifiant réseau', 1, 1),
('Protocole sécurisé', 1, 2),
('Langage de programmation', 0, 2);

-- TENTATIVE_QCM
INSERT INTO tentative_qcm (id_user, id_qcm, score)
VALUES
(3,1,80),
(3,2,60),
(4,1,40);

-- PROJET
INSERT INTO projet (titre, description, id_competence)
VALUES
('Analyse réseau', 'Analyse du trafic', 1),
('Audit sécurité web', 'Test vulnérabilités', 2);

-- SOUMISSION_PROJET
INSERT INTO soumission_projet (id_projet, id_user, fichier, statut)
VALUES
(1,3,'projet1.zip','valide'),
(2,3,'projet2.zip','en_attente'),
(1,4,'projet3.zip','en_attente');

-- CRITERE_VALIDATION
INSERT INTO critere_validation (id_competence, score_min_qcm, projet_obligatoire, description)
VALUES
(1,70,1,'QCM + projet obligatoire'),
(2,50,0,'QCM seulement');

-- VALIDATION_COMPETENCE
INSERT INTO validation_competence (id_user, id_competence)
VALUES
(3,1);

-- MESSAGE
INSERT INTO message (id_etudiant, id_formateur, contenu, reponse)
VALUES
(3,2,'Question sur le QCM','Réponse du formateur'),
(4,2,'Je ne comprends pas le projet', NULL);