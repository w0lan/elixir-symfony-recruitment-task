FROM php:8.4-cli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    ca-certificates \
    git \
    unzip \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /app

RUN mkdir -p public \
  && printf '%s\n' '<?php' 'echo "symfony: container ready";' > public/index.php

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]


