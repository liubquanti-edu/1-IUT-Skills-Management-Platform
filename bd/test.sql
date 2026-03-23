SELECT * FROM utilisateur;
SELECT * FROM competence;
SELECT * FROM formation;
SELECT * FROM qcm;

SELECT u.nom, q.titre, t.score
FROM tentative_qcm t
JOIN utilisateur u ON t.id_user = u.id_user
JOIN qcm q ON t.id_qcm = q.id_qcm;

SELECT u.nom, c.nom
FROM validation_competence v
JOIN utilisateur u ON v.id_user = u.id_user
JOIN competence c ON v.id_competence = c.id_competence;

SELECT u.nom, p.titre, s.statut
FROM soumission_projet s
JOIN utilisateur u ON s.id_user = u.id_user
JOIN projet p ON s.id_projet = p.id_projet;