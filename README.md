# Yandex Maps Reviews Scraper

Тестовое задание по интеграции отзывов об организациях из Яндекс Карты с использованием Laravel, Vue 3 и Playwright.

### Обзор

Это приложение позволяет авторизованным пользователям подключаться к странице организации в Яндекс Карты, извлекать и отображать отзывы об организации с помощью автоматизации браузера.
Поскольку Яндекс не предоставляет общедоступный API для отзывов, в этом проекте используется **Playwright-based scraping** для сбора динамических данных об отзывах.

## Функции
-  Authentication (Laravel Sanctum, single seeded user: username: demo@example.com / password: password)
-  Добавить URL-адрес организации Яндекс Карты
-  Автоматический сбор данных с использованием Node.js + Playwright
- Извлекать:
  - Average rating
  - Total ratings
  - Total reviews (~up to 600)
  - Reviews (author, date, text, rating, likes)
- Pagination (50 reviews per page from DB)
- Cached storage in MySQL (no repeated scraping)
- Hybrid scraping strategy with two different options:
  - Network interception
  - DOM parsing fallback
- Fully Dockerized setup

---
##  Scraping Strategy
### 1. DOM Scraping (parser/yandex-parser.js)
Скрепер отслеживает реальные сетевые запросы браузера:

- Страница полностью отображена.
- Отзывы извлекаются с помощью CSS-селекторов.
- Данные извлекаются из HTML-элементов.
- up to 600
- изменять App/Services/YandexReviewsParser.php  в 155
Преимущества: Работает без доступа к API.

Проблемы: при большом количестве отзывов и большом размере данных возникает проблема с совпадением времени ожидания между ответами Laravel и парсера.
---
### 2. Network Interception (parser/yandex-parser2.js)
Скрепер отслеживает реальные сетевые запросы браузера:

- Перехватывает вызовы API `fetchReviews`
- Извлекает структурированные JSON-ответы.
- Автоматически собирает отзывы с постраничной разбивкой.
- up to 500
- изменять App/Services/YandexReviewsParser.php  в 155
Преимущества: Быстрое получение более надежных и структурированных данных.
Проблемы: при большом количестве отзывов и большом размере данных возникает проблема с совпадением времени ожидания между ответами Laravel и парсера.
---
##  Database Structure

### Organizations 

- id
- business_id
- name
- url
- average_rating
- total_ratings
- total_reviews
- last_synced_at
- created_at 
- updated_at

### Reviews Table

- id
- organization_id
- author
- date
- text
- rating
- yandex_review_id
- created_at 
- updated_at

---

##  Installation (Docker)


```bash
git clone https://github.com/salmamhammad/Yandex-Reviews.git
cd Yandex-Reviews
cd frontend
npm install
cd ../
cd backend 
cp .env.example .env
composer install --no-interaction --prefer-dist --optimize-autoloader
docker compose up -d --build 
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate:fresh --seed   
```
---
---
##  Open application

Frontend: http://localhost
API: http://localhost/api
---
## Requirements
- Docker
- Docker Compose
- PHP 8.3+
- MySQL 8
---
## Environment Variables
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=root

---
## Future option
Использование ИИ-агента для анализа API или HTML-кода, возможно, быстрее, но я не пробовал. На разработку и тестирование потребуется много времени.