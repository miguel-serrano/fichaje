.PHONY: up down stop test test-filter build npm-dev npm-build front-build migrate fresh seed pint shell deploy cache-clear mysql

up:
	vendor/bin/sail up -d

down:
	vendor/bin/sail down

stop:
	vendor/bin/sail stop

test:
	vendor/bin/sail artisan test

test-filter:
	vendor/bin/sail artisan test --filter=$(filter)

build:
	vendor/bin/sail build

npm-dev:
	vendor/bin/sail npm run dev

npm-build:
	vendor/bin/sail npm run build

front-build:
	vendor/bin/sail npm run build

migrate:
	vendor/bin/sail artisan migrate

fresh:
	vendor/bin/sail artisan migrate:fresh --seed

seed:
	vendor/bin/sail artisan db:seed

pint:
	vendor/bin/sail bin pint --dirty

shell:
	vendor/bin/sail shell

cache-clear:
	vendor/bin/sail artisan cache:clear

mysql:
	docker exec -it fichaje-mysql-1 mysql -u sail -ppassword

deploy:
	./deploy-remote.sh