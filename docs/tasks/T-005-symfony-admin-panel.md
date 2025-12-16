### T-005 — Symfony Admin Panel (Users CRUD via Phoenix API)

### Cel
Zbudować panel administracyjny w `symfony-app/` do zarządzania użytkownikami przez Phoenix REST API (bez własnej bazy danych i bez encji Doctrine).

### Kontekst
- Phoenix wystawia JSON API:
  - `GET /users` (filtry + sort + paginacja)
  - `GET /users/:id`
  - `POST /users`
  - `PUT /users/:id`
  - `DELETE /users/:id`
  - `POST /import`
- `compose.yaml` przekazuje `PHOENIX_BASE_URL=http://phoenix:4000` do kontenera Symfony.

### Zakres
- Symfony 7.4.
- UI (Twig):
  - lista użytkowników z filtrami (first_name, last_name, gender, birthdate_from/to),
  - sortowanie po kolumnach (query params),
  - paginacja,
  - dodawanie użytkownika (formularz -> `POST /users`),
  - edycja użytkownika (formularz -> `PUT /users/:id`),
  - usuwanie użytkownika (`DELETE /users/:id`),
  - akcja importu (`POST /import`) jako przycisk w UI (opcjonalnie z tokenem po stronie API, jeśli włączony).

### Poza zakresem
- Własna baza/Doctrine.
- OpenAPI/Swagger.

---

### Integracja HTTP (wymagania implementacyjne)
- Konfiguracja klienta:
  - `PHOENIX_BASE_URL` jako parametr konfiguracyjny (np. `services.yaml`/`framework.http_client`),
  - jeden dedykowany serwis-klient (np. `App\PhoenixApi\PhoenixApiClient`) oparty o `Symfony\Component\HttpClient\HttpClientInterface`.
- Wszystkie wywołania do API przechodzą przez ten serwis (żadnych `HttpClientInterface` w kontrolerach bezpośrednio).
- Obsługa timeoutów i błędów transportowych: błąd sieci -> UX-friendly komunikat + sensowny status w odpowiedzi.

### Kontrakt z API (w panelu)
- `GET /users` query params:
  - `first_name`, `last_name`, `gender`, `birthdate_from`, `birthdate_to`,
  - `sort_by`, `sort_dir`,
  - `page`, `page_size`.
- Odpowiedź listy:
  - `data: User[]`
  - `meta: { page, page_size, total }`

### Mapowanie błędów
API błędy zwraca w formacie:
```json
{
  "error": {
    "code": "...",
    "message": "...",
    "details": {}
  }
}
```

Wymagane zachowanie w UI:
- `validation_error` (422): mapować `details` na błędy pól formularza (FormError),
- `not_found` (404): 404 w UI dla widoku szczegółów/edycji lub flash + redirect na listę,
- `invalid_params` (400): flash + pozostanie na liście (z zachowaniem query),
- `import_failed` (502) / `internal_error` (500): flash error.

---

### Proponowana struktura (do implementacji)
- `App\PhoenixApi\PhoenixApiClient`
  - `listUsers(UsersListQuery $query): UsersListResult`
  - `getUser(int $id): UserDto`
  - `createUser(UserInput $input): UserDto`
  - `updateUser(int $id, UserInput $input): UserDto`
  - `deleteUser(int $id): void`
  - `import(): int`
- DTO / request models:
  - `UsersListQuery` (filters + sort + pagination)
  - `UsersListResult` (`users`, `meta`)
  - `UserDto`
  - `UserInput`
- Formy:
  - `UsersFilterType` (GET form)
  - `UserType` (create/edit)

---

### Definition of Done
- `make up` uruchamia UI (port `8000`).
- Panel działa end-to-end tylko przez Phoenix API (brak DB/Doctrine dla users).
- Lista ma filtry, sortowanie i paginację zgodne z query params API.
- Create/Update/Delete działają i po sukcesie UI pokazuje wynik (redirect + flash).
- Błędy z API są obsłużone przewidywalnie i mapowane do UI/form.

### TODO
- Zachować query (filtry/sort/paginacja) po akcjach `DELETE` i `POST /import` (redirect na listę nie powinien gubić stanu).
- Ustawić sensowne statusy HTTP w UI przy błędach transportowych / timeoutach.
- Uodpornić DTO `fromArray()` na brakujące klucze (zwracać kontrolowane `invalid_response`, bez warningów).
- Wydzielić mapowanie `PhoenixApiException` -> UX (flash/redirect/render), żeby nie dublować w kontrolerach.
- Jedno źródło prawdy dla kolumn sortowania (PHP + Twig), żeby uniknąć rozjazdów.
- Wydzielić stałe/enums dla kodów błędów API i `sort_by`/`sort_dir` (ograniczyć magic strings).
- Zamiast `LogicException` przy `birthdate === null` w `UserInputFactory` — kontrolowane zachowanie albo gwarancja przez flow/typy.
