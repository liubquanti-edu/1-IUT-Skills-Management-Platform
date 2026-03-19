# Cahier des charges  
Plateforme de gestion des compétences en cybersécurité

---

## 1. Contexte du projet

Dans un contexte où les compétences en cybersécurité deviennent essentielles, cette plateforme vise à centraliser la gestion, le suivi et la validation des connaissances des utilisateurs dans ce domaine.

L’application permettra aux étudiants de progresser à travers différentes compétences, de tester leurs connaissances via des QCM, et de soumettre des projets pratiques. Les formateurs pourront accompagner cette progression en définissant les critères de validation, en proposant des formations, en validant les travaux et en répondant aux questions. L’administrateur assurera la gestion globale du système.

L’objectif principal est de créer un environnement structuré favorisant l’apprentissage, l’évaluation et le suivi des compétences en cybersécurité.

---

## 2. Acteurs

### 2.1 Administrateur
- Gère les inscriptions des utilisateurs
- Valide les comptes étudiants et formateurs
- Réinitialise les mots de passe
- Supervise le bon fonctionnement de la plateforme

### 2.2 Formateur
- Propose des contenus pédagogiques (PDF)
- Crée des QCM
- Définit les critères de validation des compétences (score minimal, validation de projet, etc.)
- Associe contenus et compétences
- Corrige et valide les projets soumis selon des critères définis
- Répond aux questions des étudiants
- Consulte les statistiques de consultation

### 2.3 Étudiant
- Accède à son espace personnel
- Consulte ses compétences
- Suit des formations
- Passe des QCM
- Soumet des projets
- Pose des questions aux formateurs

---

## 3. Besoins fonctionnels

### 3.1 Fonctionnalités communes
- Authentification (connexion sécurisée)
- Gestion du profil utilisateur
- Déconnexion

### 3.2 Fonctionnalités étudiant
- Accéder au tableau de bord
- Consulter les compétences disponibles
- Choisir une compétence
- Passer un QCM de validation
- Déposer un projet (upload de fichier)
- Consulter les formations
- Filtrer les formations par compétence
- Poser des questions aux formateurs
- Consulter les critères de validation définis par le formateur
- Consulter l’état de validation des projets et compétences

### 3.3 Fonctionnalités formateur
- Accéder à son tableau de bord
- Définir les conditions de validation d’une compétence :
  - score minimum au QCM
  - obligation ou non de projet
  - critères d’évaluation du projet
- Consulter les projets soumis
- Valider ou refuser un projet selon les critères définis
- Proposer une formation (upload PDF)
- Créer et gérer des QCM
- Associer formations/QCM à des compétences
- Consulter les questions des étudiants
- Répondre aux questions
- Visualiser les statistiques (nombre de vues)

### 3.4 Fonctionnalités administrateur
- Valider les inscriptions utilisateurs
- Gérer les comptes (activation/désactivation)
- Réinitialiser les mots de passe
- Superviser les activités globales

---

## 4. Besoins non fonctionnels

### 4.1 Sécurité
- Stockage sécurisé des mots de passe (hashage)
- Utilisation de requêtes préparées (PDO) pour éviter les injections SQL
- Gestion des sessions sécurisées
- Contrôle des accès selon les rôles

### 4.2 Performance
- Temps de réponse rapide
- Optimisation des requêtes SQL

### 4.3 Ergonomie
- Interface claire et intuitive
- Affichage compréhensible des critères de validation pour les étudiants

### 4.4 Fiabilité
- Sauvegarde des données
- Gestion des erreurs

### 4.5 Contraintes techniques
- Utilisation de PHP
- Base de données MySQL
- Frontend en HTML/CSS
- Architecture du code organisée (séparation logique)

---

## 5. Règles de gestion

- Un utilisateur doit être validé par un administrateur avant de pouvoir se connecter
- Un mot de passe doit être sécurisé et non stocké en clair
- Les critères de validation d’une compétence sont définis par le formateur
- Une compétence peut nécessiter :
  - un score minimum au QCM
  - la validation d’un projet
  - ou les deux
- Un QCM ne peut pas être repassé avant un délai de 30 jours
- Un projet soumis doit être évalué selon les critères définis par le formateur
- Une compétence est validée uniquement si toutes les conditions définies par le formateur sont remplies
- Un formateur peut associer plusieurs compétences à une formation ou un QCM
- Les fichiers déposés doivent respecter un format autorisé (PDF pour formations, fichiers définis pour projets)
- Chaque action importante doit être enregistrée dans l’historique (QCM, dépôts, validations)
- Un étudiant ne peut accéder qu’à ses propres données
- Les formateurs ne peuvent gérer que les contenus qu’ils ont créés (selon choix d’implémentation)

---

## 6. Questions de réflexion

### 6.1 Fonctionnalités communes
- Authentification
- Gestion du profil
- Consultation des informations de base

### 6.2 Actions nécessitant une authentification
- Accès au tableau de bord
- Soumission de projets
- Création de contenu
- Réponse aux questions
- Validation des projets

### 6.3 Données à conserver dans l’historique
- Résultats des QCM
- Dates de passage des QCM
- Projets soumis (date, statut)
- Validations/refus des projets
- Critères de validation définis et leurs modifications
- Questions et réponses
- Consultations des formations (pour statistiques)

---

## 7. Conclusion

Ce projet permet de mettre en pratique des compétences essentielles en développement web : conception, structuration des données, sécurité, gestion des rôles et interactions utilisateur. Il constitue une base solide pour une application complète orientée métier dans le domaine de la cybersécurité.