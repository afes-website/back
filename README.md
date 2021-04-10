# 73rd AzabuFes official website back-end

![build](https://img.shields.io/github/workflow/status/afes-website/back/Deploy%20into%20production%20server/master?label=Deploy&style=for-the-badge)
![version](https://img.shields.io/badge/dynamic/json?color=007ec6&label=version&style=for-the-badge&query=version&url=https://raw.githubusercontent.com/afes-website/back/develop/composer.json)

![Lumen](https://img.shields.io/badge/dynamic/json?color=555&label=Lumen&style=flat-square&query=require["laravel/lumen-framework"]&url=https://raw.githubusercontent.com/afes-website/back/develop/composer.json&labelColor=E74430&logo=lumen&logoColor=fff)
![PHP](https://img.shields.io/badge/dynamic/json?color=555&label=PHP&style=flat-square&query=require["php"]&url=https://raw.githubusercontent.com/afes-website/back/develop/composer.json&labelColor=777BB4&logo=php&logoColor=fff)

## Project setup
1. copy `.env.example` to `.env`
2. edit `.env`
3. run commands on below
4. publish `public/` as document root

```sh
composer install
php artisan migrage
php artisan db:seed
```


## run test
```sh
vendor/bin/phpunit
```
