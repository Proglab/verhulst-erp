# Sommaire

1. [Modification du docker-compose](#docker)
1. [Credentials](#credentials)
1. [Accès au Dashboard](#dashboard)

# Modification du docker-compose <a name="docker"></a>

```yaml
services:
    minio:
        image: minio/minio
        environment:
            MINIO_ROOT_USER: minio
            MINIO_ROOT_PASSWORD: minio123
        volumes:
            - ./data/minio:/data
        command: server /data --address ":9004" --console-address ":9001"
        ports:
            - 9004:9004
            - 9001:9001

    createbuckets:
        image: minio/mc
        depends_on:
            - minio
        entrypoint: >
            /bin/sh -c "
            /usr/bin/mc alias set myminio http://minio:9004 minio minio123;
            /usr/bin/mc mb myminio/somebucketname;
            /usr/bin/mc policy set public myminio/somebucketname;
            exit 0;
            "
```

Remplacez les valeurs de `MINIO_ROOT_USER`, `MINIO_ROOT_PASSWORD` par ce que vous voulez.

De même, remplacez `somebucketname` (2 fois) par le nom du bucket qui sera créé automatiquement.

# Credentials <a name="credentials"></a>

```php
    's3AccessId' => 'minio',
    's3AccessSecret' => 'minio123',
    's3BucketName' => 'somebucketname', // le nom du bucket que vous avez configuré ci-dessus
    's3Region' => 'us-east-1',
    's3Endpoint' => 'http://127.0.0.1:9004',
```

# Accès au Dashboard <a name="dashboard"></a>

Pour accéder au Dashboard de Minio : http://127.0.0.1:9001/

```bash
username : minio
password : minio123
```