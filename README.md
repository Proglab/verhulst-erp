### Description

Symfony 6 Skeleton est un projet servant de squelette de base pour tout projet Symfony.
Il embarque avec lui tous les vendors et packages de base nécessaires à chaque projet.
Un design est également mis en place, basé sous Bootstrap 5 : https://themes.3rdwavemedia.com/demo/portal/
Des fonctionnalités ont également été implémentées (voir ci-dessous)

### Design

- Mise en place de Bootstrap 5 et du thème Portal (https://themes.3rdwavemedia.com/demo/portal/)
- Mise en place de InkyFoundation pour envoyer des mails stylisés

### Fonctionnalités

- Mise en place d'une gestion utilisateur (inscription, connexion, vérification d'email, renvoi de lien de vérification d'email, reset password, modification de mot de passe)
- Envoi des principaux emails (inscription, vérification d'email)
- Mise en place d'un UserChecker qui bloque les utilisateurs qui n'ont pas confirmé leur compte

### Extensions twig

- **sanitize** : Utilisation de sanitize_html pour contrer les fails XSS
- **countryName** : Renvoie le nom du pays en français à partir de l'ISO
- **isEgal** : Permet de faire une condition sur l'équivalence entre deux éléments et ajouter une classe si condition vérifiée.
- **badgeFilter**: Renvoie un badge à partir du contenu passé en paramètre, avec possibilité d'ajouter des options
- **minimizeString** : Truncate une chaîne de caractères


### Voters

- ExtraVoter::IS_XML_HTTP_REQUEST ('isXmlHttpRequest') : Vérifie que la requête est une requête ajax
- ExtraVoter::IS_NOT_AUTHENTICATED ('is_not_authenticated') : Vérifie que l'utilisateur n'est pas connecté


### Validators

- Création d'un validateur permettant de vérifier qu'un mot de passe est sécurisé (SecurePasswordValidator)

### Code interne, helpers

- Création des principaux Traits pour les entités
- Mise en place de classes d'helpers (monnaie)

### Tests

Mise en place de tous les tests (controllers, entities, forms, repositories)


## Doc

### PHPCS
```
docker run --init --rm -v {DIR_TO_PROJECT}:/project -w /project jakzal/phpqa php-cs-fixer fix --config="./.php-cs-fixer.dist.php" --verbose --using-cache=no
```

### PHPStan
```
docker run --init --rm -v {DIR_TO_PROJECT}:/project -w /project jakzal/phpqa phpstan analyse src
```