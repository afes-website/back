# 73rd AzabuFes official website back-end

![build](https://img.shields.io/github/workflow/status/afes-website/back/Deploy%20into%20production%20server/master?label=Deploy&style=for-the-badge)

![Lumen](https://img.shields.io/badge/Lumen-^6.0-555.svg?labelColor=E74430&logo=lumen&style=flat-square&logoColor=fff)
![PHP](https://img.shields.io/badge/PHP-^7.2-555.svg?labelColor=777BB4&logo=php&logoColor=fff&style=flat-square)

## Project setup
1. copy `.env.example` to `.env`
2. edit `.env`
3. run commands on below
4. publish `public/` as document root

```sh
composer install
php artisan migrage
```


## run test
```sh
vendor/bin/phpunit
```
