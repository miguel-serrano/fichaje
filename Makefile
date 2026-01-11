.PHONY: up down stop test test-filter build npm-dev npm-build migrate fresh pint shell

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

migrate:
	vendor/bin/sail artisan migrate

fresh:
	vendor/bin/sail artisan migrate:fresh --seed

pint:
	vendor/bin/sail bin pint --dirty

shell:
	vendor/bin/sail shell