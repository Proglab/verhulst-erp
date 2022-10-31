# Sommaire

1. [Modification du docker-compose](#docker)
1. [Credentials](#credentials)


# Modification du docker-compose <a name="docker"></a>

```yaml
services:
  mysql:
    image: mysql:8
    container_name: '!ChangeMe!'
    command: --default-authentication-plugin=mysql_native_password
    environment:
      MYSQL_DATABASE: '!ChangeMe!'
      MYSQL_ROOT_PASSWORD: root
    ports:
      - 3307:3307
    networks:
      - monProjetSymfony

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: phpmyadmin
    environment:
      PMA_HOST: mysql
    ports:
      - 1235:80
    networks:
      - monProjetSymfony
    depends_on:
      - mysql

networks:
    monProjetSymfony:
```

Remplacez les variables suivantes :

```bash
MYSQL_DATABASE : Par le nom de la base de données
container_name : Par le nom à donner au container (symfony_[project_name] par exemple)
Toutes les mentions à 'monProjetSymfony' par le nom que vous souhaitez donner au réseau qui sera créé
```

# Credentials <a name="credentials"></a>

**Mysql** :

```bash
host : 127.0.0.1:3307
user : root
mdp: root

database : celle que vous avez choisie
```

**Phpmyadmin** :

```bash
url : http://0.0.0.0:1236/
user : root
mdp: root
```