<img src="https://i.imgur.com/ocGeE9u.png" style="width: 400px; margin: 0 auto;">

# eazyonline.nl

> Moderne, modulaire webplatformen voor EazyOnline, gebouwd met **Laravel 12**, **TailwindCSS** en **Alpine.js**.  
> Focus op snelheid, schaalbaarheid en een **guided** UX (onboarding, previews, AI-assists).

![PHP](https://img.shields.io/badge/PHP-8.3-777?logo=php)
![Laravel](https://img.shields.io/badge/Laravel-12-red?logo=laravel)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3-06B6D4?logo=tailwindcss)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3-77C1D2)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql)
![Node](https://img.shields.io/badge/Node-20-339933?logo=nodedotjs)
![License](https://img.shields.io/badge/License-Commercial-111)

---

## Inhoudsopgave
- [Technische stack](#technische-stack)
- [Snel starten](#snel-starten)

---

## Technische stack
- **Back-end:** Laravel 12, PHP 8.3, MySQL 8
- **Front-end:** TailwindCSS 3, Alpine.js 3, GSAP Animaties, Swiper.js
- **Build:** Vite
- **Dev:** Laragon
- **CI/CD:** GitHub Actions
- **Hosting:** Plesk/VM of containerized

---


## Snel starten
```bash
# 1) Project klonen
git clone <repo-url> eazyonline
cd eazyonline

# 2) Afhankelijkheden
composer require
npm install

# 3) Env & app key
cp .env.example .env
php artisan key:generate

# 4) Database
# Pas DB_* variabelen in .env aan
php artisan migrate --force
php artisan db:seed

# 5) Lanceren
php artisan optimize
php artisan config:clear
npm run dev
