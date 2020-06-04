![Vonage][logo]

# Building a Befriending Service with Symfony

With the current global situation, most countries are in some form of lockdown. Social distancing is critical right now to reduce the impact of Covid-19. But sadly, at the same time, those people who don't have a large pool of people to call during their days trapped indoors. At Vonage, we have regular opportunities to build something for our learning. In this opportunity, I chose to create a befriending service, which would introduce users that are vulnerable, lonely or want a different person to talk to daily. The idea behind this is to enable people to make new friends while in lockdown, or at any time.

The `master` branch is the starting branch for the accompanying post at: [Blog post url here](#)

**Table of Contents**

- [Prerequisites](#prerequisites)
- [Getting Started](#getting-started)
  - [Clone the Repository](#clone-the-repository)
  - [Environment Variables](#environment-variables)
  - [Run Docker](#run-docker)
  - [Run Database Migrations](#run-database-migrations)
  - [Install Third Party Libraries](#install-third-party-libraries)
  - [Test Run the Application](#test-run-the-application)
- [Code of Conduct](#code-of-conduct)
- [Contributing](#contributing)
- [License](#license)

## Prerequisites

- [Docker](https://www.docker.com/)
- [Node Package Manager (NPM)](https://www.npmjs.com/get-npm)
- [A Vonage (formally Nexmo) account](https://dashboard.nexmo.com/sign-up?utm_source=DEV_REL&utm_medium=github&utm_campaign=befriending-service-with-symfony)
- [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)

## Getting Started

### Cloning the Repository

Run the following two commands to clone this repository and change directory into the repository directory.

```
git clone git@github.com:nexmo-community/befriending-service-with-symfony.git
cd befriending-service-with-symfony
```

### Environment Variables

Within the `project/` directory create a `.env.local` file, which will be where you store your local environment variables you don't wish to be committed to your repository. For example, your database connection settings. Copy the following lines into your `.env.local` file:

```
DATABASE_URL=mysql://user:password@mysql:3306/befriending?serverVersion=8.0.17&charset=utf8

VONAGE_API_KEY=<api_key>
VONAGE_API_SECRET=<api_secret>
VONAGE_BRAND_NAME="Befriending"

MAP_QUEST_API_KEY=<map_quest_api_key>
MAP_QUEST_API_URL=<map_quest_api_secret>

VONAGE_APPLICATION_ID=<application_id>
VONAGE_APPLICATION_PRIVATE_KEY_PATH=/var/www/symfony/private.key
VONAGE_NUMBER=<vonage_number>

NGROK_URL=<ngrok url>
```

Be sure to replace the values with your credentials.

### Run Docker

Within the `docker/` directory run: `docker-compose up -d`.

Once completed should be shown the confirmation that the three containers are running.

### Run Database Migrations

In your terminal, connect to the bash prompt from within the PHP Docker container by running the following command:

```
docker-compose exec php bash
```

Then run to create the database tables by running the command below. Which will take all of the migration files found in `symfony/src/migrations/` and execute them. For this example it creates a user database table with the relevant columns.

```
php bin/console doctrine:migrations:migrate
```

### Install Third Party Libraries

Several third party libraries already defined and need to be installed, via both Composer and Yarn.

Run the following command inside the `project/` directory:

```
composer install
yarn install
yarn run dev
```

### Test Run the Application

Go to: [http://localhost:8081/register/](http://localhost:8081/register) in your browser, you should be greeted with a registration page.

If you're at this point, you're all set up to use the project.

## Code of Conduct

In the interest of fostering an open and welcoming environment, we strive to make participation in our project and our community a harassment-free experience for everyone. Please check out our [Code of Conduct][coc] in full.

## Contributing
We :heart: contributions from everyone! Check out the [Contributing Guidelines][contributing] for more information.

[![contributions welcome][contribadge]][issues]

## License

This project is subject to the [MIT License][license]

[logo]: vonage_logo.png "Vonage"
[contribadge]: https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat "Contributions Welcome"

[coc]: CODE_OF_CONDUCT.md "Code of Conduct"
[contributing]: CONTRIBUTING.md "Contributing"
[license]: LICENSE "MIT License"

[issues]: ./../../issues "Issues"
