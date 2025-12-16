FROM php:8.4-cli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    ca-certificates \
    git \
    unzip \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /app

ENV APP_ENV=dev
ENV APP_DEBUG=1
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --no-progress --no-scripts

COPY . .
RUN test -f .env || : > .env

EXPOSE 8000

CMD ["php", "-d", "variables_order=EGPCS", "-S", "0.0.0.0:8000", "-t", "public"]


