# Sleep Journal 😴

A Laravel app for logging nightly sleep, tracking daily habits, and finding out
which habits actually improve (or hurt) how well you sleep.

## Features

- **Sleep log** - record bedtime, wake time, quality (1-5) and notes; overnight
  sleeps and duration are handled automatically
- **Dashboard** with averages for the last 7 days, 30 days and all time
- **Consistency metrics** - standard deviation of duration, bedtime and wake time
- **Ideal sleep profile** - a quality weighted bedtime, wake time and duration
  derived from your above average nights
- **Interactive charts** for duration, quality, bedtime and wake time
  (30 days / 90 days / all time)
- **Best and worst night**
- **Habit insights** - compares nights with vs. without each habit and flags it as
  helpful, harmful or neutral (requires ≥ 5 nights on each side)
- **Habit management** - create and delete your own habits and tag them on entries
- **Authentication** - registration, email verification, password reset, profile
  management (Laravel Breeze)
- **Authorization** - policies ensure users can only access their own data

## Tech stack

- **Backend:** PHP, Laravel
- **Frontend:** Blade, Tailwind CSS, Vite, Chart.js
- **Database:** MySQL

## Getting started

**Requirements:** PHP 8.3+, Composer, Node.js 20.19+, MySQL

```bash
git clone https://github.com/maybelizard/Sleep-Journal.git
cd Sleep-Journal
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create an empty database (e.g. `sleep_journal`) and set it in `.env`:

```env
DB_CONNECTION=mysql
DB_DATABASE=sleep_journal
DB_USERNAME=<your user>
DB_PASSWORD=<your password>
```

Then:

```bash
php artisan migrate --seed
npm run dev          # terminal 1
php artisan serve    # terminal 2
```

Open http://localhost:8000 and sign in with the seeded demo account:

| Email | Password |
|-------|----------|
| demo@example.com | password |

The seeder generates 60 nights of sample data, so the dashboard and charts are populated straight away.