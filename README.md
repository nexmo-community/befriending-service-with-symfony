![Vonage][logo]

# Building a Befriending Service with Symfony

With the current global situation, most countries are in some form of lockdown. Social distancing is critical right now to reduce the impact of Covid-19. But sadly, at the same time, those people who don't have a large pool of people to call during their days trapped indoors. At Vonage, we have regular opportunities to build something for our learning. In this opportunity, I chose to create a befriending service, which would introduce users that are vulnerable, lonely or want a different person to talk to daily. The idea behind this is to enable people to make new friends while in lockdown, or at any time.

The `master` branch is the starting branch for the accompanying post at: [Blog post url here](#)

**Table of Contents**

- [Prerequisites](#prerequisites)
- [Getting Started](#getting-started)
  - [Clone the Repository](#clone-the-repository)
  - [Database Credentials](#database-credentials)
  - [Run Docker](#run-docker)
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

### Clone the Repository

Run the following two commands to clone this repository and change directory into the repository directory.

```
git clone git@github.com:nexmo-community/befriending-service-with-symfony.git
cd befriending-service-with-symfony
```

### Database Credentials

Within the `project/` directory create a `.env.local` file, which will be where you store your local environment variables you don't wish to be committed to your repository. For example, your database connection settings. Copy the following line into your `.env.local` file:

```
DATABASE_URL=mysql://user:password@mysql:3306/befriending?serverVersion=8.0.17&charset=utf8
```

### Run Docker

Within the `docker/` directory run: `docker-compose up -d`.

Once completed should be shown the confirmation that the three containers are running.

### Install Third Party Libraries

Several third party libraries already defined and need to be installed, via Composer.

Run the following command inside the `docker/` directory:

```
docker-compose exec php composer install
```

### Test Run the Application

Go to: [http://localhost:8081/](http://localhost:8081) in your browser, you should be greeted with Symfony's default template.

If you're at this point, you're all set up and ready for this tutorial.

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
