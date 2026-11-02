# Lumen PHP Framework

## Deploying the API on Vercel

This Lumen API needs a PHP server; a static Vite deployment only returns Vercel `NOT_FOUND` for its `/api/*` routes. `Dockerfile.vercel`, `Caddyfile`, and `vercel.json` configure the app to run in Vercel's container runtime.

Set these Vercel environment variables before deploying:

- `APP_KEY` — a stable Laravel-compatible key.
- `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — credentials for the existing MySQL database. The database host must be reachable from Vercel; `127.0.0.1` refers to the container.
- `JWT_SECRET` — a strong, stable secret used to sign student access tokens.
- `JWT_EXPIRATION_MS` — token lifetime in milliseconds, for example `3600000`.
- `APP_URL` — the deployed API URL.

After deploying the API, set the student frontend's `BACKEND_API_URL` to its public base URL ending in `/api`, for example `https://<api-domain>/api`.

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel/lumen-framework)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/lumen-framework)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://img.shields.io/packagist/l/laravel/lumen)](https://packagist.org/packages/laravel/lumen-framework)

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

> **Note:** In the years since releasing Lumen, PHP has made a variety of wonderful performance improvements. For this reason, along with the availability of [Laravel Octane](https://laravel.com/docs/octane), we no longer recommend that you begin new projects with Lumen. Instead, we recommend always beginning new projects with [Laravel](https://laravel.com).

## Official Documentation

Documentation for the framework can be found on the [Lumen website](https://lumen.laravel.com/docs).

## Contributing

Thank you for considering contributing to Lumen! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Security Vulnerabilities

If you discover a security vulnerability within Lumen, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
