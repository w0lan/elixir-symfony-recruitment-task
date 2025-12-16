### Projekt
Zadanie rekrutacyjne: **Phoenix (Elixir) + Symfony (PHP)**, komunikacja po **REST API (JSON)**.

### Uruchomienie (Docker)

Wymagania: Docker, Docker Compose, Make.

1. Uruchomienie aplikacji:
   ```bash
   make up
   # lub
   docker compose up -d --build
   ```

2. Lista wszystkich dostępnych komend Make:
   ```bash
   make help
   ```

2. Dostępne usługi:
   - **Symfony App (Frontend)**: http://localhost:8000
   - **Phoenix API (Backend)**: http://localhost:4000
   - **Swagger UI (Dokumentacja API)**: http://localhost:8080

### Jakość kodu i Testy

Projekt zawiera skonfigurowane narzędzia QA i testy (PHP CS Fixer, PHPStan, ExUnit, PHPUnit).

**Testy:**
```bash
make test          # Uruchamia testy Phoenix i Symfony
make test-phoenix  # Tylko testy backendu
make test-symfony  # Tylko testy frontendu
```

**Lintery i Analiza Statyczna:**
```bash
make lint          # Uruchamia PHP CS Fixer (dry-run) i PHPStan
make fix-cs        # Automatycznie naprawia styl kodu (PHP CS Fixer)
```

### Źródła danych
Import użytkowników korzysta z danych z rejestrów PESEL:
- **Imiona**: https://dane.gov.pl/pl/dataset/1501,lista-imion-wystepujacych-w-rejestrze-pesel
- **Nazwiska**: https://dane.gov.pl/pl/dataset/1681,nazwiska-osob-zyjacych-wystepujace-w-rejestrze-pesel

### Struktura
- `phoenix-api/`: Backend (Elixir/Phoenix) + PostgreSQL.
- `symfony-app/`: Frontend/Admin Panel (Symfony 7.4).
- `docs/`: Dokumentacja projektu i specyfikacja OpenAPI.
