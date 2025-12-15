### T-004 — Phoenix Import (`POST /import`)

### Cel
Dodać endpoint `POST /import` w `phoenix-api/`, który pobiera dane źródłowe (PESEL imiona/nazwiska), wyznacza top 100 dla każdej płci, generuje 100 losowych użytkowników i zapisuje ich do PostgreSQL.

### Kontekst
- Phoenix + Postgres działają w Dockerze (`make up`).
- Istnieje model `users` oraz API CRUD.

### Zakres
- Endpoint: `POST /import`.
- Pobranie danych:
  - “Imiona w rejestrze PESEL” (osobno dla `male` i `female`).
  - “Nazwiska w rejestrze PESEL”.
- Wyznaczenie top 100:
  - imiona: top 100 dla `male` oraz top 100 dla `female`,
  - nazwiska: top 100 (jeśli źródło nie rozróżnia płci, ta sama lista jest używana dla obu płci).
- Generacja 100 użytkowników:
  - losowe połączenie `first_name + last_name`,
  - `gender` spójny z imieniem,
  - `birthdate` losowo z zakresu `1970-01-01` – `2024-12-31`,
  - zapis do PostgreSQL.
- Spójne odpowiedzi błędów (format jak w T-003).

### Poza zakresem
- Zmiany w Symfony.
- OpenAPI/Swagger.

---

### Konfiguracja
- Źródła danych są konfigurowalne przez ENV (brak hardcode w kodzie):
  - `PESEL_MALE_FIRST_NAMES_URL`
  - `PESEL_FEMALE_FIRST_NAMES_URL`
  - `PESEL_LAST_NAMES_URL`
- Zabezpieczenie endpointu (opcjonalne):
  - jeśli `IMPORT_TOKEN` jest ustawiony, `POST /import` wymaga nagłówka `Authorization: Bearer <IMPORT_TOKEN>`.

### Źródła danych (referencje)
- Nazwiska (PESEL): `https://dane.gov.pl/pl/dataset/1681,nazwiska-osob-zyjacych-wystepujace-w-rejestrze-pesel`
- Imiona (PESEL): `https://dane.gov.pl/pl/dataset/1501,lista-imion-wystepujacych-w-rejestrze-pesel`

---

### Kontrakt API

#### `POST /import`
- 200:
```json
{
  "data": {
    "inserted": 100
  }
}
```

Błędy:
- 401 `unauthorized` (gdy wymagany token i jest brak/błędny),
- 502 `import_failed` (np. brak dostępu do źródeł / błąd parsowania),
- 500 `internal_error`.

---

### Definition of Done
- `POST /import` działa w Dockerze po `make up`.
- Po wywołaniu `POST /import` w bazie pojawia się 100 nowych rekordów w tabeli `users` spełniających warunki.
- Dla błędów (token, parametry/konfiguracja, pobieranie źródeł) zwracane są spójne błędy JSON i właściwe kody HTTP.
