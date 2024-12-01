### Local Install

#### Create `.env.local`
```bash
cat << EOF > .env.local
MYSQL_ROOT_PASSWORD=root
MYSQL_ROOT_USER=root
MYSQL_DATABASE=kasta_db
MYSQL_PORT=3317

APP_ENV=dev
APP_SECRET=f7350192a342ffb1ac0c377941fc846fbedb40635d6d069a0649bec0b1d44ce8
DATABASE_URL=mysql://root:root@mysql_kasta_db/kasta_db
EOF
```

#### Install the project
```bash
docker compose -f docker-compose-local.yaml --env-file ./.env.local build --no-cache              # Build containers from images
```
```bash
docker compose -f docker-compose-local.yaml --env-file ./.env.local up -d                         # Run containers       
```              
```bash
docker exec -it kastapay-php_kasta-1 composer install --optimize-autoloader                        # Install dependencies
```
```bash
docker exec -it kastapay-php_kasta-1 php bin/console doctrine:database:create --if-not-exists       # Create database
```
```bash
docker exec -it kastapay-php_kasta-1 php bin/console doctrine:migrations:migrate -n                 # Run migrations
```
```bash
docker exec -it kastapay-php_kasta-1 php bin/console doctrine:fixtures:load -n                      # Load fixtures
```
```bash
docker exec -it kastapay-php_kasta-1 symfony serve -d --allow-all-ip
```
```bash
docker exec -it kastapay-php_kasta-1 vendor/bin/phpunit --testdox                                   # Generate JWT keys
```

#### Open in browser
http://127.0.0.1:8000/api/doc

http://127.0.0.1:8000/api/doc.json






