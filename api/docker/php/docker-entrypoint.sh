#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
	if [ "$APP_ENV" != 'prod' ]; then
		PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
	fi
	ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"

	mkdir -p var/cache var/log
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

	if [ "$APP_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-interaction --ignore-platform-reqs
	fi

	if grep -q DATABASE_URL= .env; then
		echo "Waiting for database to be ready..."
		ATTEMPTS_LEFT_TO_REACH_DATABASE=60
		until [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ] || DATABASE_ERROR=$(php bin/console doctrine:query:sql -q "SELECT 1" 2>&1); do
			if [ $? -eq 255 ]; then
				# If the Doctrine command exits with 255, an unrecoverable error occurred
				ATTEMPTS_LEFT_TO_REACH_DATABASE=0
				break
			fi
			sleep 1
			ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
			echo "Still waiting for database to be ready... Or maybe the database is not reachable. $ATTEMPTS_LEFT_TO_REACH_DATABASE attempts left."
		done

		if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
			echo "The database is not up or not reachable:"
			echo "$DATABASE_ERROR"
			exit 1
		else
			echo "The database is now ready and reachable"

			if [ "$APP_ENV" != 'prod' ]; then
				echo "The databases will be drop if exist"

				php bin/console doctrine:database:drop --force --no-interaction
				php bin/console doctrine:database:create --no-interaction
				echo "The database api was dropped"
			fi

			if $(php bin/console doctrine:query:sql -q "SELECT 1" -e test); then
				php bin/console doctrine:database:drop -e test --force --no-interaction
				echo "The database api_test was dropped"
			fi
				php bin/console doctrine:database:create -e test --no-interaction
				php bin/console doctrine:migrations:migrate -e test --no-interaction
				echo "The database api_test was created"
		fi

		if ls -A migrations/*.php >/dev/null 2>&1; then
			php bin/console doctrine:migrations:migrate --no-interaction
		fi

		if [ "$APP_ENV" != 'prod' ]; then
        		php bin/console haute:fixture:load --no-interaction
		fi
	fi
fi

exec docker-php-entrypoint "$@"
