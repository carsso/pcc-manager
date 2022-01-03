# OVHcloud PCC web interface

Web interface displaying realtime OVHcloud PCC infrastructure data (virtual machines, hosts and datastores health & resource usage).

Not affiliated with OVHcloud.

Written in PHP/Laravel and VueJS. _Depends on the [OVHcloud API wrapper](https://github.com/ovh/php-ovh)_

### Screenshots:

#### Datacenter view:
![Datacenter view](https://user-images.githubusercontent.com/666182/147897550-666653e9-d7e7-4528-bda9-1a8be3dc6265.png)

## Deployment

#### Clone repository : 
```sh
git clone https://github.com/carsso/pcc-manager.git
```

#### Switch to deploy branch :
```sh
git fetch origin deploy
```

#### Copy default env file :
```sh
cp .env.example .env
```

#### Fill the env file :
```sh
vim .env
```
_Read https://github.com/ovh/php-ovh#supported-apis to find the URL to generate the script credentials and the supported endpoints_

#### Install dependencies based on lock file
```sh
composer install --no-interaction --prefer-dist --optimize-autoloader
```

#### Clear cache
```sh
php artisan optimize
```

## Development

#### Pre-requisites
- PHP >= 7.4
- NodeJS >= 12

#### Clone repository (main branch) : 
```sh
git clone git@github.com:carsso/pcc-manager.git
```

Install PHP dependencies with Composer :
```sh
composer install
```

Install JS dependencies with NPM :
```sh
npm install
```

#### Copy default env file :
```sh
cp .env.dev.example .env
```

#### Fill the env file :
```sh
vim .env
```
_Read https://github.com/ovh/php-ovh#supported-apis to find the URL to generate the script credentials and the supported endpoints_

#### Build js and css files:
```sh
npm run dev
```

#### Build js and css files automatically while developing :
```sh
npm run watch
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).