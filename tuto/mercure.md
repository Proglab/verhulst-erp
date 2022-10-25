1. [Installation de Mercure](#mercure)
   1. [Installation des dépendances avec Composer](mercure-composer)
   1. [Modification du docker-compose](mercure-docker)
   1. [Modification des variables d'environnement](mercure-env)
1. [Installation de RabbitMQ](#rabbitmq)
    1. [Extension PHP AMQP](rabbitmq-php)
    1. [Installation des dépendances avec Composer](rabbitmq-composer)
    1. [Modification du docker-compose](rabbitmq-docker)
    1. [Modification des variables d'environnement](rabbitmq-env)
    1. [Connexion au dashboard RabbitMQ](rabbitmq-dashboard)
    1. [Configuration de Messenger](rabbitmq-messenger)
1. [Utilisation](#utilisation)
    1. [Création de l'écouteur sur le fichier Twig](utilisation-twig)
    1. [Création de l'event](utilisation-event)
    1. [Création du handler](utilisation-handler)
    1. [Ajout de l'event dans le routing de Messenger](utilisation-messenger)
    1. [Lancement de l'event](utilisation-call-event)
    1. [Démarrage de RabbitMQ](utilisation-lancement-commande)

# Installation de Mercure <a name="mercure"></a>

## Installation des dépendances avec Composer <a name="mercure-composer"></a>

```bash
composer require symfony/mercure-bundle
```

## Modification du docker-compose <a name="mercure-docker"></a>

Le recipe de **symfony/mercure-bundle** devrait avoir modifié les fichiers **docker-composer.yml** et **docker-compose.override.yml** :

**docker-compose.yml** :
```yaml
  ###> symfony/mercure-bundle ###
  mercure:
    image: dunglas/mercure
    restart: unless-stopped
    environment:
      SERVER_NAME: ':80'
      MERCURE_PUBLISHER_JWT_KEY: '!ChangeMe!'
      MERCURE_SUBSCRIBER_JWT_KEY: '!ChangeMe!'
      # Set the URL of your Symfony project (without trailing slash!) as value of the cors_origins directive
      MERCURE_EXTRA_DIRECTIVES: |
        cors_origins https://127.0.0.1:8000
    # Comment the following line to disable the development mode
    command: /usr/bin/caddy run -config /etc/caddy/Caddyfile.dev
    volumes:
      - mercure_data:/data
      - mercure_config:/config
  ###< symfony/mercure-bundle ###
```

**docker-compose.override.yml** :

```yaml
services:
###> symfony/mercure-bundle ###
  mercure:
    ports:
      - "80"
###< symfony/mercure-bundle ###
```

Faites attention à ce que `MERCURE_EXTRA_DIRECTIVES:cors_origins` corresponde bien à votre domaine. (https, domaine, port)

Modifiez le fichier **docker-compose.override.yml** afin d'affecter des ports fixes à Mercure :

```yaml
###> symfony/mercure-bundle ###
  mercure:
    ports:
      - "5400:80"
###< symfony/mercure-bundle ###
```

## Modification des variables d'environnement <a name="mercure-env"></a>

Ces variables ont été ajoutées :

```bash
MERCURE_URL=https://example.com/.well-known/mercure
MERCURE_PUBLIC_URL=https://example.com/.well-known/mercure
MERCURE_JWT_SECRET="!ChangeThisMercureHubJWTSecretKey!"
```

Modifiez la variable `MERCURE_URL` : `MERCURE_URL="http://127.0.0.1:5400/.well-known/mercure"`
et `MERCURE_PUBLIC_URL` : `MERCURE_PUBLIC_URL="https://127.0.0.1:5400/.well-known/mercure"`

Maintenant, Mercure tournera sur le port 5400, même si vous redémarrez le conteneur Docker.

Ajoutez également la variable d'environnement `MERCURE_JWT_SECRET` dans votre **.env.local** et mettez ce que vous voulez.
Il s'agit du token qui sera utilisé pour permettre au serveur d'utiliser Mercure.

# Installation de RabbitMQ <a name="rabbitmq"></a>

Pour utiliser Mercure de manière optimale, il faudra le coupler à RabbitMQ.

## Extension PHP AMQP <a name="rabbitmq-php"></a>

Si vous n'avez pas l'extension php amqp, il faudra l'installer.

**Debian/Ubuntu** : `sudo apt install php8.1-amqp` (remplacez 8.1 par votre version de PHP)

**Windows** : https://pecl.php.net/package/amqp

## Installation des dépendances avec Composer <a name="rabbitmq-composer"></a>

`composer require symfony/amqp-messenger`

## Modification du docker-compose <a name="rabbitmq-docker"></a>

Ajoutez ceci au **docker-compose.yml** sous la clé `services`:

```yaml
rabbitmq:
image: rabbitmq:3.7-management
ports:
  - '5672:5672'
  - '15672:15672'
```

**RabbitMQ** écoutera automatiquement sur le port **5672**.

## Modification des variables d'environnement <a name="rabbitmq-env"></a>

Ajoutez ceci au fichier **.env** :

`RABBITMQ_DSN="amqp://guest:guest@127.0.0.1:5672"`

## Connexion au dashboard RabbitMQ <a name="rabbitmq-dashboard"></a>

http://127.0.0.1:15672/#/

```
identifiant : guest
mdp : guest
```

# Configuration de Messenger <a name="rabbitmq-messenger"></a>

Dans le fichier **config/packages/messenger.yaml** :

```yaml
framework:
    messenger:
#        failure_transport: failed

        transports:
            # https://symfony.com/doc/current/messenger.html#transport-configuration
            async:
                dsn: '%env(RABBITMQ_DSN)%'
                retry_strategy:
                    max_retries: 3
                    multiplier: 2
#            failed: 'doctrine://default?queue_name=failed'

        routing:
            Symfony\Component\Mercure\Update: async

            # Route your messages to the transports
            # 'App\Message\YourMessage': async
```

Sous la clé `routing`, nous allons faire pointer nos futurs events.

# Utilisation <a name="utilisation"></a>

Exemple avec la création d'un **Post** (entité Post):

## Création de l'écouteur sur le fichier Twig <a name="utilisation-twig"></a>

On met en place un écouteur depuis un fichier Twig :

```js
{% block mercure %}
    <script>
        const eventSource = new EventSource("{{ mercure('new_post_index')|escape('js') }}");
        eventSource.onmessage = event => {

            // Will be called every time an update is published by the server
            let data = JSON.parse(event.data);
            toastr['success'](`Nouveau post : ${data['title']}`);
        }
    </script>
{% endblock %}
```

Pensez à ajouter au **base.html.twig** le block `{% block mercure %}{% endblock %}` juste en dessous du block
`{% block javascripts %}{% endblock %}`.

On donne un nom à l'écouteur, ici : `new_post_index`.
Et dans le corps de l'évènement, on récupère le data renvoyé par **Mercure** (dans notre cas, il contiendra 
les données du **Post** que l'on va créer plus bas, en **JSON**). Dès lors, on peut faire le traitement désiré en **JavaScript**.
Dans notre cas, on affiche simplement une notification.

## Création de l'event <a name="utilisation-event"></a>

**src/Messenger/Event/NewPost.php**:

```php
<?php

namespace App\Messenger\Event;

class NewPost
{
    public function __construct(
        private readonly string $title,
        private readonly string $content
    )
    {
    }

    public function getTitle(): string
    {
        return $this->title;
    }


    public function getContent(): string
    {
        return $this->content;
    }
}
```

## Création du handler <a name="utilisation-handler"></a>

**src/Messenger/Handler/NewPostHandler.php** :

```php
<?php

namespace App\Messenger\Handler;

use App\Entity\Post;
use App\Messenger\Event\NewPost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class NewPostHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly MessageBusInterface $bus,
    )
    {
    }

    public function __invoke(NewPost $newPost)
    {
        $post = new Post();
        $post->setTitle($newPost->getTitle());
        $post->setContent($newPost->getContent());
        $this->manager->persist($post);
        $this->manager->flush();

        $update = new Update(
            'new_post_index',
            json_encode([
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
            ])
        );

        $this->bus->dispatch($update);
    }
}
```

Nous enverrons un évènement `NewPost` depuis un contrôleur, service... et le handler va capter l'évènement et le traiter.
Dans notre cas, on crée un nouveau **Post** à partir des données du **NewPost**.
Une fois cela fait, on envoie un **Update** grâce à Mercure, que l'on va distribuer avec RabbitMQ grâce à ` $this->bus->dispatch($update)`.

Dans le code suivant :

```php
$update = new Update(
    'new_post_index',
    json_encode([
        'id' => $post->getId(),
        'title' => $post->getTitle(),
        'content' => $post->getContent(),
    ])
);
```

`new_post_index` correspond à l'endroit où l'update sera envoyé (voir ci-dessous).

Le second paramètre, contenant le **Post** encodé en JSON, sera le contenu que nous pourrons récupérer depuis les
fichiers Twig à partir desquels nous ferons les traitements (voir ci-dessus).

## Ajout de l'event dans le routing de Messenger <a name="utilisation-messenger"></a>

Dans le fichier **config/packages/messenger.yaml**, sous la clé `routing`, ajoutez l'évènement en async :

```yaml
        routing:
            App\Messenger\Event\NewPost: async
```

## Lancement de l'event <a name="utilisation-call-event"></a>

Depuis un contrôleur, un service, ou ce que vous souhaitez, on appelle l'event :

```php
    #[Route('/maRoute', name: 'app_post_event', methods: ['GET', 'POST'])]
    public function createPostsWithRabbit(MessageBusInterface $bus): Response
    {
        // Votre code de manière normale comme dans n'importe quelle action ...
        
        // Dispatch de l'event NewPost auquel on joint le titre et le contenu
        $bus->dispatch(new NewPost('title', 'content'));

        return $this->redirectToRoute('app_post_index');
    }
```

## Démarrage de RabbitMQ <a name="utilisation-lancement-commande"></a>

`php bin/console messenger:consume async -vv`