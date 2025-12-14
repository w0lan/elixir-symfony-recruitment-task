### T-002 — Phoenix bootstrap (app + DB)

### Cel
Zastąpić obecny „stub” w `phoenix-api/` właściwą aplikacją Phoenix, podłączoną do PostgreSQL z `compose.yaml`.

### Kontekst
Projekt ma 2 aplikacje: Phoenix (backend) + Symfony (frontend). Backend ma wystawić REST API dla użytkowników oraz endpoint importu. Ten task dotyczy wyłącznie przygotowania fundamentu Phoenix (bez pełnego API i bez importu).

### Zakres
- Utworzenie projektu Phoenix w katalogu `phoenix-api/` (z Ecto + Postgres).
- Konfiguracja połączenia do DB na podstawie `DATABASE_URL` z `compose.yaml`.
- Migracje + schema dla tabeli `users` (pola wg `docs/kryteria-akceptacji.md`).
- Uruchamianie serwera Phoenix w Dockerze (kontener `phoenix` realnie nasłuchuje na `:4000`).
- Endpoint health (do healthcheck w `compose.yaml`) w ramach Phoenix.

### Poza zakresem (na ten task)
- Import danych (PESEL) i `POST /import`.
- Pełne REST API CRUD + filtrowanie/sortowanie.
- OpenAPI/Swagger.
- Zmiany w Symfony.

### Definition of Done
- `make up` uruchamia DB + Phoenix bez ręcznych kroków „z palca”.
- Kontener `phoenix` nasłuchuje na porcie `4000` i zwraca poprawną odpowiedź na endpoint health.
- Migracje tworzą tabelę `users` w Postgres (zgodnie ze specyfikacją).
- Aplikacja Phoenix łączy się z DB wyłącznie przez konfigurację środowiskową (bez sekretów w repo).
- Aktualny „stub” (pliki typu `dev_server.exs`, `www/*`) jest usunięty lub zastąpiony implementacją Phoenix.

---

### Informacje dla agenta (Cursor / AI)

#### Dlaczego ten task robi AI
- Użytkownik realizuje zadanie rekrutacyjne i chce wykonać część Phoenix/Elixir **z użyciem AI**, ponieważ **nie zna Elixira** na poziomie pozwalającym samodzielnie dowieźć całość.

#### Zasady pracy
- Działaj tylko w ramach tego taska, bez rozszerzania zakresu.
- Nie commituj zmian bez akceptacji użytkownika.
- Dokumentacja po polsku, kod i nazewnictwo w kodzie po angielsku.
- Nie dodawaj komentarzy do kodu.

#### Styl współpracy z użytkownikiem (ważne)
- Zanim zaczniesz implementację, **wytłumacz użytkownikowi co zamierzasz zrobić i dlaczego** (w prostych słowach, bez kodowania).
- W trakcie pracy odpowiadaj na pytania użytkownika i doprecyzowuj niejasności.
- Po przygotowaniu zmian wykonaj **code review własnych zmian** (krótko: ryzyka, trade-offy, co warto zweryfikować) przed prośbą o akceptację i commit.

#### Tryb pracy w Cursor
- Używamy trybu **Agent** (nie “Ask”), tak żeby agent mógł:
  - czytać pliki w repo,
  - edytować wiele plików,
  - uruchamiać polecenia w terminalu (build/run/test),
  - przygotować zmiany jako diff do review.
- Agent nie wykonuje działań w git (commit/push) bez wyraźnej prośby użytkownika.

#### Oczekiwane zmiany w repo (orientacyjnie)
- `phoenix-api/` zawiera standardową strukturę projektu Phoenix.
- `phoenix-api/Dockerfile` i `compose.yaml` uruchamiają Phoenix realnie (bez „sleep infinity”).
- `compose.yaml` healthcheck dla `phoenix` wskazuje na endpoint health w Phoenix.

#### Kryteria jakości
- Konfiguracja uruchomieniowa jest prosta i powtarzalna.
- Brak ostrzeżeń/obejść typu „zrób ręcznie X po starcie”, o ile nie są absolutnie konieczne.

#### Prompty (do udokumentowania współpracy z AI)
Poniższe prompty są przykładową formą wejścia dla agenta; mogą być wklejone do historii chatu lub zapisane jako artefakt pracy.

**Prompt startowy (T-002)**:
- Kontekst: Repo zawiera `compose.yaml` z usługami `db`, `phoenix`, `symfony`. Obecny `phoenix` to stub, ale ma działać prawdziwy Phoenix + Ecto + Postgres.
- Cel: Utwórz aplikację Phoenix w `phoenix-api/`, podłącz Postgres (ENV `DATABASE_URL`) i dodaj migracje + schema `users`.
- Ograniczenia: Nie implementuj importu i CRUD API; nie commituj; dokumentacja po polsku, kod po angielsku; bez komentarzy w kodzie.
- Definition of Done: `make up` uruchamia DB + Phoenix; jest endpoint health używany przez `compose.yaml`; migracje tworzą tabelę `users`.


