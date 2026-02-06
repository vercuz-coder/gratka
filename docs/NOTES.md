# Notatki z Realizacji Zadania

## Metodologia i Narzędzia
W projektach, w tym hobbistycznych, programuję głównie z wykorzystaniem : **Antigravity**, **ClaudeCode** oraz **Cursor**. Pracuję na modelach **Opus 4.5**, **Sonnet 4.5** oraz **Gemini 3 Pro** (wersje Pro i Flash). Przygotowuje pliki PRD, wgrywam skille którę będą przydatne dla danego techstacka oraż używam serwerów MCP takich jak context7 do przygotowania implementation planów. Ponadto używam CI/CD w GithubActions oraz gemini-code-assist jako dodatkową CR. 

Przy tym projekcie zastosowałem pełne wsparcie AI do efektywnej pracy:
*   Przygotowanie rulesy .gemini do Antigravity oraz tech-stack.md
*   Wykorzystanie serwerów **MCP** (np. `context7`) do generowania *implementation plans*.

> **Kontekst:** Pani Natalia kładła duży nacisk na moje uczestnictwo w szkoleniu **AI 10xDevs**, dlatego przy realizacji tego zadania zastosowałem metodykę, którą wykorzystuję w mojej codziennej pracy.

## Implementacja Techniczna

### Architektura i Integracje
*   **Phoenix API**: Utworzyłem `PhoenixClientInterface` oraz `PhoenixClient` (bazując na sugestiach z `services_test.yaml`) do obsługi połączeń zewnętrznych.
*   **Moduł Likes**: Przerobiłem na standardową strukturę używaną w pozostałych częściach systemu.
*   **Konfiguracja Docker**:
    *   Zmodyfikowałem `docker-compose.yml`, aby korzystał ze zmiennych z pliku `.env`.
    *   Dodałem `.env.example`. (W środowisku produkcyjnym zalecane jest użycie mechanizmu np. *secrets*).

### Nowe Funkcjonalności
*   **Filtrowanie zdjęć**: Zaimplementowane na stronie głównej (podejście **KISS**). Możliwości rozwoju: AJAX, paginacja, indeksy fulltext.
*   **Like Button**: Przerobiony na **AJAX** – umożliwia lajkowanie bez przeładowania strony i utraty ustawionych filtrów.
*   **Git Flow**: Stworzyłem branch `staging`, do którego mergowane były poszczególne zadania.

### Narzędzia Deweloperskie (DX)
Wprowadziłem zestaw narzędzi podnoszących jakość kodu i wygodę pracy:
1.  **Makefile**: Podstawowe komendy do zarządzania projektem (ułatwienie pracy na WSL/Windows).
2.  **PHP-CS-Fixer**: Skonfigurowany pod Symfony i PHP 8.1.
3.  **PHPStan**: Statyczna analiza kodu.
4.  **PHPUnit**: Testy oraz raporty pokrycia kodu (coverage).

### Testy
Testy (jednostkowe, integracyjne, funkcjonalne) generowałem przy wsparciu dokumentacji oraz modeli **Opus 4.5 / Gemini 3**. Warto je uzupełnić.

---

## Wskazówki: Docker i Środowisko

*   **Reset bazy**: Po usunięciu wolumenów (`down -v`) należy ponownie uruchomić migracje dla obu baz danych.
*   **Baza testowa**: Testy korzystają z osobnej bazy `instashot_test`, która wymaga oddzielnego utworzenia i migracji (`--env=test`).

---

## Plany na Przyszłość (TODO)

Gdybym dysponował większym czasem, zrealizowałbym następujące usprawnienia:

*   **Zależności**: Podbicie wersji paczek w `composer.json`.
*   **Bezpieczeństwo**: Szyfrowanie `PhoenixApiKey` w bazie danych (obecnie plaintext + maskowanie w UI).
*   **CI/CD**: Rozbudowa pipeline o Code Quality (phpcsfixer, phpstan) i testy blokujące merge do stagingu.
*   **Logika Importu**:
    *   Obsługa duplikatów (aktualizacja pól zamiast pomijania).
    *   Rozszerzenie modelu `Photos` o dodatkowe dane z API (lens, settings).
    *   Dodanie pola `importedAt`.
*   **Profil Użytkownika**: Rozbudowa `/profile` o formularz pełnej edycji danych.
