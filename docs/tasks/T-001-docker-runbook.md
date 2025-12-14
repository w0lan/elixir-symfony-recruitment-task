### T-001 — Uruchomienie (Docker/Runbook)

**Cel**: uruchomienie całego stacka jednym poleceniem oraz minimalny “smoke-check”.

**Założenia**:
- Używamy **Docker Compose v2 / Compose Specification**: plik `compose.yaml`, bez pola `version` (przykład z PDF jest w starszym formacie).
- Konfiguracja usług przez zmienne środowiskowe; bez sekretów w repo.

**Definition of Done**:
- W repo istnieje `compose.yaml`, który uruchamia co najmniej:
  - Postgres,
  - Phoenix API,
  - Symfony.
- W repo istnieje `Makefile` z komendami uruchomieniowymi “z palca” (min. `make up`, `make down`, `make logs`).
- README opisuje minimalne kroki uruchomienia oraz weryfikacji, że:
  - API działa,
  - UI działa.


