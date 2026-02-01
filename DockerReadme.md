# pour build le container docker
# (uniquement nécessaire la première fois ou lorsque vous modifiez le Dockerfile)
```
docker-compose build
```

# pour lancer le composant docker
```
docker-compose up -d --build
```

# pour arrêter le composant docker
```
docker-compose down
```

# Supprimer les volumes docker
```
docker volume rm $(docker volume ls -q)
```

# Lister les composants docker
```
docker ps
```

# aller en terminal dans le docker  :
```
docker exec -it my-php-container /bin/bash
docker exec -it site-partoches /bin/bash

cat /tmp/xdebug.log
```


# Si tu veux réimporter le SQL sans supprimer les données existantes manuellement, tu peux le faire avec la commande suivante :
```
docker exec -i my-mariadb mysql -uroot -proot site_perso < site_perso.sql
```