### T-003 — Phoenix Users CRUD API (JSON)

### Cel
Dostarczyć REST API (JSON) dla zasobu `users` w `phoenix-api/`, zgodne z kryteriami akceptacji: CRUD + filtrowanie/sortowanie + paginacja + spójne błędy.

### Kontekst
- Phoenix jest już uruchamialny w Dockerze i ma bazę Postgres (`compose.yaml`).
- Ten task buduje API potrzebne pod panel Symfony (Symfony jest poza zakresem).

### Zakres
- Endpointy:
  - `GET /users`
  - `GET /users/:id`
  - `POST /users`
  - `PUT /users/:id`
  - `DELETE /users/:id`
- `GET /users` wspiera:
  - filtrowanie po: imieniu, nazwisku, płci, dacie urodzenia (od–do),
  - sortowanie po każdej kolumnie,
  - paginację.
- Walidacje wejścia (body + query params) oraz spójny format błędów (JSON).

### Poza zakresem
- `POST /import`.
- Zmiany w `symfony-app/`.
- OpenAPI/Swagger (osobny task).

---

### Ścieżki / routing
- CRUD działa **bez prefiksu**: `/users`, `/users/:id`.
- Healthcheck pozostaje pod: `GET /health.json` (z T-002).

### Kontrakt API

#### Model `User`
- `id` (integer)
- `first_name` (string)
- `last_name` (string)
- `birthdate` (date, `YYYY-MM-DD`)
- `gender` (`male|female`)
- `inserted_at` (datetime, ISO-8601)
- `updated_at` (datetime, ISO-8601)

Uwagi dot. persystencji:
- `gender` jest persystowany w DB jako `string` o wartościach `male|female`; w aplikacji mapowany przez `Ecto.Enum`.

#### `GET /users`
Query params:
- `first_name` (opcjonalnie, dopasowanie case-insensitive, substring)
- `last_name` (opcjonalnie, dopasowanie case-insensitive, substring)
- `gender` (opcjonalnie, `male|female`)
- `birthdate_from` (opcjonalnie, `YYYY-MM-DD`)
- `birthdate_to` (opcjonalnie, `YYYY-MM-DD`)

Sortowanie:
- `sort_by` (opcjonalnie): `id|first_name|last_name|birthdate|gender|inserted_at|updated_at`
- `sort_dir` (opcjonalnie): `asc|desc`
- Domyślnie: `sort_by=id&sort_dir=asc`

Zasady walidacji sortowania:
- Brak `sort_by` i/lub `sort_dir`: użyć wartości domyślnych.
- Niepoprawne `sort_by` lub `sort_dir`: zwrócić 400 `invalid_params`.

Paginacja:
- `page` (opcjonalnie, integer >= 1; domyślnie 1)
- `page_size` (opcjonalnie, integer 1..100; domyślnie 20)

Odpowiedź 200:
```json
{
  "data": [
    {
      "id": 123,
      "first_name": "Jan",
      "last_name": "Kowalski",
      "birthdate": "1990-01-31",
      "gender": "male",
      "inserted_at": "2025-12-15T12:00:00Z",
      "updated_at": "2025-12-15T12:00:00Z"
    }
  ],
  "meta": {
    "page": 1,
    "page_size": 20,
    "total": 1
  }
}
```

Znaczenie `meta.total`:
- `total` oznacza liczbę rekordów **po zastosowaniu filtrów** i **przed paginacją**.

#### `GET /users/:id`
- 200: `{"data": <User>}`
- 404: błąd `not_found`

#### `POST /users`
Body:
```json
{
  "first_name": "Jan",
  "last_name": "Kowalski",
  "birthdate": "1990-01-31",
  "gender": "male"
}
```
- 201: `{"data": <User>}`
- 422: błąd `validation_error`

#### `PUT /users/:id`
Body jak w `POST /users`.
- 200: `{"data": <User>}`
- 404: błąd `not_found`
- 422: błąd `validation_error`

#### `DELETE /users/:id`
- 204: bez body
- 404: błąd `not_found`

---

### Format błędów
Wspólny format:
```json
{
  "error": {
    "code": "...",
    "message": "...",
    "details": {}
  }
}
```

Kody i statusy:
- `not_found` -> 404
- `validation_error` -> 422 (np. mapowanie błędów per pole w `details`)
- `invalid_params` -> 400 (np. błędny `sort_by`, nieparsowalna data, `page=0`, `birthdate_from > birthdate_to`)

---

### Definition of Done
- `make up` uruchamia stack; endpointy odpowiadają na porcie `4000`.
- CRUD działa end-to-end (zapisy i odczyty przez Ecto/Postgres).
- `GET /users` ma filtrowanie, sortowanie i paginację zgodnie z kontraktem.
- Dla błędnych danych/wejścia API zwraca spójne odpowiedzi błędów (JSON) i właściwe kody HTTP.
