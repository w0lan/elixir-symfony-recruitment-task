### Kryteria akceptacji — zadanie rekrutacyjne (Phoenix + Symfony)

### 1) Architektura
- **Dwie aplikacje**: `phoenix-api` (backend) + `symfony-app` (frontend/panel) komunikują się po **REST API (JSON)**.
- **Baza danych**: PostgreSQL.

### 2) Uruchomienie (Docker/Runbook)
- **Docker Compose**: repo zawiera `docker-compose.yml` uruchamiający:
  - Postgres,
  - Phoenix,
  - Symfony,
  - oraz mapujący porty jak w dokumentacji projektu.
- **Weryfikacja**: uruchomienie projektu zgodnie z README na “czystej” maszynie deweloperskiej jest możliwe bez ręcznych obejść.

### 3) Phoenix — model danych i persystencja
- Istnieje tabela `users` w PostgreSQL z polami:
  - `first_name` (string),
  - `last_name` (string),
  - `birthdate` (date),
  - `gender` (enum/logicznie ograniczone do `male|female`).
- **Weryfikacja**: migracje tworzą strukturę, a aplikacja odczytuje/zapisuje dane przez Phoenix/Ecto.

### 4) Phoenix — import danych
- Backend pobiera dane źródłowe:
  - “Imiona w rejestrze PESEL”
  - “Nazwiska w rejestrze PESEL”
  - i wykorzystuje je do wyznaczenia **top 100 imion i nazwisk dla każdej płci**.
- Generuje **100 losowych użytkowników**:
  - losowe połączenie imię+nazwisko,
  - płeć zgodna z imieniem,
  - data urodzenia losowana z zakresu **1970-01-01 – 2024-12-31**,
  - zapis do PostgreSQL.
- Udostępnia endpoint **`POST /import`**, który uruchamia import (może być zabezpieczony tokenem).
- **Weryfikacja**: po wywołaniu `POST /import` w bazie znajdują się dane spełniające warunki, a endpoint zwraca poprawną odpowiedź HTTP.

### 5) Phoenix — REST API (JSON)
- Dostępne endpointy:
  - `GET /users`
  - `GET /users/:id`
  - `POST /users`
  - `PUT /users/:id`
  - `DELETE /users/:id`
- `GET /users` wspiera:
  - filtrowanie po: imieniu, nazwisku, płci, dacie urodzenia (od–do),
  - sortowanie po **każdej** kolumnie.
- **Weryfikacja**: każdy endpoint działa zgodnie z intencją CRUD, a filtrowanie/sortowanie wpływa na wynik listy.

### 6) Symfony — panel administracyjny (bez własnej bazy/encji)
- Symfony pobiera/zmienia dane **wyłącznie przez REST API Phoenix** (np. `HttpClient`).
- Funkcjonalności w UI:
  - lista użytkowników z filtrowaniem (imię, nazwisko, płeć, zakres dat urodzenia),
  - sortowanie po kolumnach (query params),
  - dodawanie (POST),
  - edycja (PUT),
  - usuwanie (DELETE).
- **Weryfikacja**: wszystkie operacje w panelu wywołują odpowiednie endpointy w Phoenix i odzwierciedlają wynik w UI.

### 7) Repo i dokumentacja oddania
- Repo zawiera:
  - README z instrukcją uruchomienia (Docker),
  - linki do oryginalnych źródeł danych (PESEL imiona/nazwiska).
- **Weryfikacja**: osoba oceniająca jest w stanie uruchomić i sprawdzić działanie bez dodatkowej wiedzy “z głowy”.

---

### 8) Kontrakt API i obsługa błędów
- API ma spójne odpowiedzi błędów (kody HTTP + struktura JSON), a walidacje wejścia są jednoznaczne.
- Krytyczne przypadki (np. nieistniejący `:id`, błędny payload, błędne parametry filtrów) są obsłużone przewidywalnie.

### 9) Powtarzalność i utrzymanie
- Konfiguracja środowiskowa jest oparta o zmienne środowiskowe (bez sekretów w repo).
- Kod jest czytelny i uporządkowany, a odpowiedzialności są rozdzielone (łatwe utrzymanie).

### 10) Jakość weryfikacji (testy lub plan testów)
- Krytyczne ścieżki mają pokrycie testami **albo** istnieje jasno spisany plan testów akceptacyjnych (scenariusze + dane + oczekiwane rezultaty).

---

### 11) Specyfikacja API (OpenAPI/Swagger)
- Istnieje specyfikacja API w formacie OpenAPI/Swagger (wraz z przykładami).

### 12) Paginacja listy użytkowników
- Lista użytkowników jest stronicowana (paginacja).

### 13) Logowanie/trace requestów
- Jest ujednolicone logowanie/trace requestów (łatwiejszy debug).

---
