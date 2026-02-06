# Tech Stack Projektu

Projekt składa się z dwóch głównych aplikacji (Phoenix API oraz Symfony App) działających w konteneryzowanym środowisku Docker.

## Główna Infrastruktura (Docker)
Cały system jest orkiestrowany za pomocą `docker-compose`.
- **Konteneryzacja**: Docker & Docker Compose
- **Bazy Danych**: 
  - `phoenix-db`: PostgreSQL 15 (dla aplikacji Phoenix)
  - `symfony-db`: PostgreSQL 15 (dla aplikacji Symfony)

## 1. Phoenix API (`phoenix-api`)
Aplikacja backendowa napisana w języku Elixir, wykorzystująca framework Phoenix.
- **Język**: Elixir `~> 1.15`
- **Framework**: Phoenix `~> 1.7`
- **Baza Danych**: PostgreSQL (obsługiwana przez Ecto)
- **Kluczowe biblioteki**:
  - `phoenix_ecto`, `ecto_sql` - warstwa dostępu do danych
  - `req` - klient HTTP
  - `nimble_csv` - obsługa plików CSV
  - `jason` - obsługa JSON
  - `gettext` - internacjonalizacja (i18n)

## 2. Symfony App (`symfony-app`)
Aplikacja webowa napisana w PHP, oparta na frameworku Symfony.
- **Język**: PHP `^8.1`
- **Framework**: Symfony `6.4.*`
- **Baza Danych**: PostgreSQL (obsługiwana przez Doctrine ORM)
- **Frontend**: Server-side rendering z użyciem Twig (brak osobnego builda frontendowego JS typu React/Vue w tej strukturze)
- **Kluczowe biblioteki**:
  - `doctrine/orm` - ORM do obsługi bazy danych
  - `symfony/http-client` - klient HTTP
  - `symfony/security-bundle` - uwierzytelnianie i autoryzacja
  - `symfony/twig-bundle` - silnik szablonów
  - `symfony/validator` - walidacja danych

## Komunikacja
- Aplikacja Symfony komunikuje się z Phoenix API (zdefiniowana zmienna środowiskowa `PHOENIX_BASE_URL`).
- Obie aplikacje działają w sieci wewnętrznej Dockera i wystawiają porty na zewnątrz:
  - Phoenix: port `4000`
  - Symfony: port `8000`
