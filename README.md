# PHPong

![PHP](https://img.shields.io/badge/PHP-v8.1+-828cb7.svg?style=flat-square&logo=php)
![Laravel](https://img.shields.io/badge/Laravel-v10.10+-f55247.svg?style=flat-square&logo=laravel)
![Livewire](https://img.shields.io/badge/Livewire-v3.4+-00d88e.svg?style=flat-square&logo=laravel)
![Alpine.js](https://img.shields.io/badge/Alpine.js-v2.8+-8bc0d0.svg?style=flat-square&logo=alpine.js)
![NPM](https://img.shields.io/badge/NPM-v9.5+-cb3837.svg?style=flat-square&logo=npm)
[![MIT Licensed](https://img.shields.io/github/license/noweh/phpong)](licence.md)

Source code for PHPong.

![PHPong.jpg](assets%2FPHPong.jpg)


## How to play:

- Use the arrow keys to move the paddle.
- Press longer on arrow keys to move faster.
- Press the space bar to start the game.
- Press the space bar to pause the game.

![pause-and-play.gif](assets%2Fpause-and-play.gif)

10 points are awarded for each time the ball hits the paddle. The game ends when the ball hits the left wall.
A level is completed for every 20 points scored. The game will then increase in difficulty.

![move-and-collision.gif](assets%2Fmove-and-collision.gif)


## BACK-END INSTALL

### Requirement

You will need to have the following installed:

- [Composer](https://getcomposer.org/)
- [PHP](https://www.php.net/)

To install the back-end vendors, launch the following command:

```bash
cd project
composer install
```

### .env file

The `.env` file is mandatory to set up the site. The file should be located in the root of the project.
Copy the `.env.example` file and rename it to `.env`.

### Cache

To clear the cache, run the following command:

```bash
sh scripts/refresh_cache.sh
```

## FRONT-END INSTALL

### Requirement

You will need to have the following installed:

- [Node.js](https://nodejs.org/en/)
- [NPM](https://www.npmjs.com/)

To compile the front-end assets, launch the following command to install the dependencies:

```bash
npm run development
```
Or, for minified assets:
```bash
npm run production
```

You should now see two new files within your projexts `public` folder:
- .public/css/app.min.css
- .public/js/app.min.js

To watch for changes, run the following command:

```bash
npm run start
```