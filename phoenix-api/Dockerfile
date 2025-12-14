FROM elixir:1.19

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    build-essential \
    ca-certificates \
    curl \
    git \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /app

RUN mix local.hex --force && mix local.rebar --force

COPY dev_server.exs /app/dev_server.exs
COPY www /app/www

CMD ["elixir", "dev_server.exs"]


