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

ENV MIX_ENV=dev

COPY mix.exs mix.lock ./
RUN mix deps.get
RUN mix deps.compile

COPY .formatter.exs ./
COPY config config
COPY lib lib
COPY priv priv
COPY test test

EXPOSE 4000

CMD ["sh", "-lc", "mix ecto.create && mix ecto.migrate && mix phx.server"]


