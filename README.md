# Fonctionnement
1. Charger le fichier htpass.php à la racine du serveur
2. Appleler le script via son URL (ex : https://exemple.com/htpass.php)
3. Renseigner les champs :
   - Identifiant
   - Mot de passe
   - Liste des IP autorisées
4. Le fichier .htpasswd est automatiquement créé avec le couple identifiant / mot de passe crypté
5. Le code est automatiquement ajouté au fichier .htaccess 
6. Renseignez la fiche 1password
7. Renseigner la fiche Notion du projet