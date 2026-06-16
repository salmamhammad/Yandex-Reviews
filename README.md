# Yandex Maps Reviews Scraper

Test assignment for integrating Yandex Maps organization reviews using Laravel, Vue 3, and Playwright.

### Overview

This application allows authenticated users to connect a Yandex Maps organization page, extract and display organization reviews from using browser automation.  
Since Yandex does not provide a public reviews API, this project uses **Playwright-based scraping** to collect dynamic review data.

## Features
-  Authentication (Laravel Sanctum, single seeded user: username: demo@example.com / password: password)
-  Add Yandex Maps organization URL
-  Automatic scraping using Node.js + Playwright
- Extract:
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
The scraper listens to real browser network requests:

- Page is fully rendered
- Reviews are extracted via CSS selectors
- Data is parsed from HTML elements
- up to 600
- change App/Services/YandexReviewsParser.php  in 155
advantages: Works without API access

problems: matching with timeout between the laravel and parser resposnse become problem with high number of reviews and big size

---
### 2. Network Interception (parser/yandex-parser2.js)
The scraper listens to real browser network requests:

- Captures `fetchReviews` API calls
- Extracts structured JSON responses
- Collects paginated reviews automatically
- up to 500
- change App/Services/YandexReviewsParser.php  in 155
advantages: Fast and return more reliable and stractured data

problems: matching with timeout between the laravel and parser resposnse become problem with high number of reviews and big size

---
##  Database Structure

### Organizations 

- id
- name
- yandex_url
- average_rating
- total_ratings
- total_reviews
- address
- created_at 
- updated_at

### Reviews Table

- id
- organization_id
- author
- rating
- date
- text
- likes
- external_review_id

---

##  Installation (Docker)

### 1. Clone repository

```bash
git clone https://github.com/your-repo/yandex-scraper.git
cd yandex-scraper
docker-compose up -d --build
```
---
### 2. Install Laravel dependencies
```bash
docker exec -it yandex_php bash
composer install
php artisan key:generate
php artisan migrate --seed
```
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
using AI agent to analysis API or html code, maybe faster but not try it . it need much time to build and test.