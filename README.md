[![License](https://img.shields.io/badge/License-MIT-blue.svg)](http://opensource.org/licenses/MIT)

# OVHcloud PCC web interface

Web interface displaying realtime OVHcloud PCC (Private Cloud / Hosted Private Cloud) infrastructure data (virtual machines, hosts and datastores health & resource usage).

Test it live on [pcc-manager.com](https://pcc-manager.com)

_Inspired by the great OVHcloud vScope <sup>
[link](https://help.ovhcloud.com/csm/en-vmware-using-vscope?id=kb_article_view&sysparm_article=KB0045667)</sup> interface. Not affiliated with OVHcloud._

Written in PHP/Laravel and VueJS. _Depends on [OVHcloud APIs PHP wrapper](https://github.com/ovh/php-ovh) and my [OVHcloud Provider for OAuth 2.0 Client](https://github.com/carsso/oauth2-ovhcloud)_

### Products compatibility

#### Compatible OVHcloud products :
- Dedicated Cloud
- Private Cloud
- SDDC
- Hosted Private Cloud Premier <sup>powered by VMware</sup>
- Hosted Private Cloud <sup>powered by VMware
[link](https://www.ovhcloud.com/en/enterprise/products/hosted-private-cloud/)</sup>
- Managed Bare Metal Essentials <sup>powered by VMware
[link](https://www.ovhcloud.com/en/managed-bare-metal/)</sup>

### Screenshots:

#### Datacenter view:
![Datacenter view](https://user-images.githubusercontent.com/666182/156908567-49930926-796d-47bf-b026-5e1347432626.png)

![Datacenter view](https://user-images.githubusercontent.com/666182/156908568-f45b3c47-c28f-4aba-84e2-8c83e110097e.png)

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
_Read https://github.com/carsso/php-ovhcloud#supported-endpoints to find the URL to generate the script credentials and the supported endpoints_

#### Install dependencies based on lock file
```sh
composer install --no-interaction --prefer-dist --optimize-autoloader
```

#### Clear cache
```sh
php artisan optimize
```

#### Create the storage symbolic links
```sh
php artisan storage:link
```

## Development

#### Pre-requisites
- PHP >= 8.1
- NodeJS >= 18

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
_Read https://github.com/carsso/php-ovhcloud#supported-endpoints to find the URL to generate the script credentials and the supported endpoints_

#### Create the storage symbolic links
```sh
php artisan storage:link
```

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