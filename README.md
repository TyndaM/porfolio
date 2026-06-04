# Portfolio Meshoulam Tinda

Portfolio professionnel statique avec une section contact serveur en PHP.

## Structure

- `index.html` : page d’accueil avec présentation, compétences, projets, services et appel à l’action.
- `about.html` : profil, coordonnées publiques et compétences.
- `resume.html` : formation, expérience et méthode de travail.
- `services.html` : services proposés.
- `portfolio.html` : projets documentés sans liens inventés.
- `contact.html` : formulaire de contact.
- `forms/contact.php` : traitement serveur du formulaire.
- `assets/css/main.css` : styles personnalisés.
- `assets/js/main.js` : navigation mobile, animations légères et gestion du formulaire.

## Fonctionnalité de contact

Le formulaire envoie les messages par email via PHP `mail()`. Les messages ne sont pas stockés dans le site. Le visiteur renseigne son nom, son email, le sujet et le message. Le script ajoute l’adresse du visiteur dans `Reply-To`, ce qui permet de répondre directement depuis la boîte mail.

### Variables d’environnement

Configurer ces variables sur l’hébergement PHP :

```bash
CONTACT_RECEIVER_EMAIL=tindameshoullam@gmail.com
CONTACT_FROM_EMAIL=no-reply@votre-domaine.com
CONTACT_SITE_NAME="Portfolio Meshoulam Tinda"
```

Notes :

- `CONTACT_RECEIVER_EMAIL` reçoit les messages.
- `CONTACT_FROM_EMAIL` doit idéalement être une adresse autorisée par votre hébergeur ou votre domaine.
- Aucune clé API privée n’est exposée côté client.
- Si l’hébergeur ne configure pas l’envoi mail, `mail()` peut échouer. Dans ce cas, activer le service mail de l’hébergeur ou remplacer le script par un relais serveur comme Resend, SMTP/Nodemailer ou Formspree côté backend.

## Sécurité minimale du formulaire

Le script PHP applique :

- validation des champs obligatoires ;
- validation du format email ;
- limites de longueur ;
- champ honeypot anti-spam ;
- limite de soumission par adresse IP avec un fichier temporaire sans contenu de message ;
- envoi uniquement par méthode `POST`.

## Test local

Depuis la racine du projet :

```bash
php -S 127.0.0.1:8000
```

Ouvrir ensuite :

```text
http://127.0.0.1:8000
```

Le rendu HTML/CSS/JS peut être vérifié localement. L’envoi réel du formulaire dépend de la configuration mail PHP de la machine ou de l’hébergement.

## Mise à jour des projets

La page `portfolio.html` n’invente pas de liens GitHub ou de démos. Ajouter un lien uniquement lorsqu’un dépôt public ou une démo existe réellement.
