# Sommaire

1. [Modification du docker-compose](#docker)
1. [Installation de FosElasticaBundle](#installation)
1. [Modification des variables d'environnement](#env)
1. [Création de l'entité](#entity)
1. [Configuration de l'index](#index-index)
1. [Création de l'index](#index-create)
1. [Population de l'index](#index-populate)
1. [Enregistrement du Finder](#finder)
1. [Creation du Helper](#helper)
1. [Faire une recherche](#search)
1. [Faire une recherche avancée avec un Repository](#repository)
1. [Paginer les résultats avec KnpPaginatorBundle](#pagination)
1. [Documentations](#doc)


# Modification du docker-compose <a name="docker"></a>

```yaml
services:
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:7.9.3
    environment:
      - cluster.name=docker-cluster
      - bootstrap.memory_lock=true
      - discovery.type=single-node
      - "ES_JAVA_OPTS=-Xms512m -Xmx512m" # 512mo HEAP
    ulimits:
      memlock:
        soft: -1
        hard: -1
    ports:
      - 9200:9200

  kibana:
    image: docker.elastic.co/kibana/kibana:7.9.3
    environment:
      ELASTICSEARCH_URL: http://127.0.0.1:9200
    depends_on:
      - elasticsearch
    ports:
      - 5601:5601
```

Pour accéder au Dashboard de Kibana : http://0.0.0.0:5601/app/home

# Installation de FosElasticaBundle <a name="installation"></a>

`composer require friendsofsymfony/elastica-bundle`

# Modification des variables d'environnement <a name="env"></a>

```bash
###> friendsofsymfony/elastica-bundle ###
ELASTICSEARCH_URL=http://localhost:9200/
###< friendsofsymfony/elastica-bundle ###
```

# Création de l'entité <a name="entity"></a>

Pour l'exemple, nous allons prendre une entité **Post**

```php
#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'posts')]
    private Collection $tags;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->tags = new ArrayCollection();
    }

    // getters & setters...
```

# Configuration de l'index <a name="index-config"></a>

Dans le fichier `config/packages/fos_elastica.yaml` :

```yaml
# Read the documentation: https://github.com/FriendsOfSymfony/FOSElasticaBundle/blob/master/doc/setup.md
fos_elastica:
    clients:
        default: { url: '%env(ELASTICSEARCH_URL)%' }
    indexes:
        posts:
            use_alias: true
            properties:
                id: ~
                title: ~
                content: ~
                user: ~
                tags: ~
                createdAt:
                    type: date
            persistence:
                driver: orm
                model: App\Entity\Post
                repository: App\ElasticSearch\Repository\EsPostRepository
```

# Création de l'index <a name="index-create"></a>

`php bin/console fos:elastica:create`

# Population de l'index <a name="index-populate"></a>

`php bin/console fos:elastica:populate`

# Enregistrement du Finder <a name="finder"></a>

Dans le fichier `config/services.yaml`, configurez le Finder pour chaque index créé :

```yaml
services:
    # default configuration for services in *this* file
    _defaults:
        autowire: true      # Automatically injects dependencies in your services.
        autoconfigure: true # Automatically registers your services as commands, event subscribers, etc.
        bind:
            FOS\ElasticaBundle\Finder\TransformedFinder $postsFinder: '@fos_elastica.finder.posts'
```

Cela doit suivre cette nomenclature :

`FOS\ElasticaBundle\Finder\TransformedFinder $[indexName]Finder: '@fos_elastica.finder.[indexName]'`

# Création du Helper <a name="helper"></a>

```php
<?php

namespace App\Helper;

use FOS\ElasticaBundle\HybridResult;

class ElasticSearchHelper
{
    /**
     * @param HybridResult[] $results
     */
    public static function toArray(array $results): array
    {
        $array = [];

        foreach($results as $result)
        {
            $array[] = $result->getTransformed();
        }

        return $array;
    }
}
```

# Faire une recherche <a name="search"></a>

Depuis un contrôleur, un service ou autre :

```php
    public function someAction(TransformedFinder $postsFinder) : Response
    {
        $limit = 9999;

        $search = Util::escapeTerm('chaîne à rechercher');
        $result = $this->postsFinder->findHybrid($search, $limit); //
        return ElasticSearchHelper::toArray($result);
        
        return $this->render("views/empty.html.twig");
    }
```

Attention, cela ne réalisera pas un LIKE à l'intérieur des champs.
Par exemple, si un des champs vaut `Lorem ipsum dolore`, il trouvera l'enregistrement si vous passez en **query**
`lorem`, `ipsum` ou `dolore`, mais pas quelque chose comme `lore`.

# Faire une recherche avancée avec un Repository <a name="repository"></a>

Créez votre repository :

```php
<?php

namespace App\ElasticSearch\Repository;

use App\Entity\Post;
use DateTimeInterface;
use Elastica\Query\BoolQuery;
use Elastica\Query\Range;
use FOS\ElasticaBundle\Repository;

class EsPostRepository extends Repository
{
    /**
     * @return Post[]
     */
    public function findByQuery($searchText): array
    {
        return $this->find($searchText);
    }

    /**
     * @return Post[]
     */
    public function findBefore(DateTimeInterface $dateTime): array
    {
        $formatted = $dateTime->format('Y-m-d');
        $boolQuery = new BoolQuery();
        $boolQuery->addMust(new Range('createdAt', ['lte' => $formatted]));
        return $this->find($boolQuery, 50);
    }

    public function findBetween(string $startDate, string $endDate, int $limit = 10): array
    {
        $boolQuery = new BoolQuery();
        $boolQuery->addMust(new Range('createdAt', ['gte' => $startDate, 'lte' => $endDate]));
        return $this->find($boolQuery, $limit);
    }
}
```

Voir la doc pour les méthodes et champs possibles.

Pour l'utiliser :

```php
    use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;

    public function someAction(RepositoryManagerInterface $repositoryManager): void
    {
        /** @var EsPostRepository $repository */
        $repository = $repositoryManager->getRepository(Post::class);

        if($this->startDate && $this->endDate) {
            $this->posts = $repository->findBetween($this->startDate, $this->endDate, $this->limit);
        } else {
            $this->posts = [];
        }

    }
```

# Paginer les résultats avec KnpPaginatorBundle <a name="pagination"></a>

```php
    public function __construct(
        private PaginatorInterface                  $paginator,
        private readonly TransformedFinder          $postsFinder,
    ){}

    public function getPosts(): PaginationInterface
    {
        $postsLines = $this->postsFinder->createPaginatorAdapter('Terme à rechercher', [
            'search_type' => 'query_then_fetch',
        ]);

        return $this->paginator->paginate(
            $postsLines,
            $this->page,
            5
        );
    }
```

# Documentations <a name="doc"></a>

- https://github.com/FriendsOfSymfony/FOSElasticaBundle/blob/master/doc/setup.md
- https://github.com/FriendsOfSymfony/FOSElasticaBundle/blob/master/doc/usage.md