# JobBoard

JobBoard is a website that helps people find jobs and helps companies find the right people to hire. Its purpose is simple: bring both sides together in one place, so hiring becomes faster, clearer and less stressful for everyone.

Job seekers can create a personal profile, browse job offers and apply in just a few clicks. They no longer need to send applications by email or chase companies for answers. Everything is kept in their account, ready to be updated at any time.

Companies can publish their openings, whether permanent positions, temporary contracts, internships or freelance missions. They receive applications directly on the platform and can view each candidate’s profile and contact details without searching through scattered messages.

Administrators oversee the whole platform. They manage users, companies, offers and applications from a single dashboard, keeping the site reliable and well organized.

Each person sees only what is relevant to their role, and clear confirmation messages guide them after every action. Personal data is protected, and users can delete their account whenever they wish.

In short, JobBoard makes recruitment simpler, more transparent and more accessible.

## Table of Contents

- [How it works](#how-it-works)
- [What makes it useful](#what-makes-it-useful)
- [In one sentence](#in-one-sentence)
- [Symfony Docker](#symfony-docker)
  - [Getting Started](#getting-started)
  - [Infos](#infos)
  - [Features](#features)
  - [Docs](#docs)
  - [License](#license)
  - [Credits](#credits)

## How it works

- **Job seekers** create a profile, browse job offers and apply in a few clicks.
- **Companies** publish their openings, whether permanent, temporary, internships or freelance work, and review the applications they receive.
- **Administrators** keep everything running smoothly by managing users, companies, offers and applications from one place.

## What makes it useful

- A simple, clear way to search and apply for jobs
- Recruiters can view candidate profiles and contact details in one place
- Everyone sees only what is relevant to their role
- Users get clear confirmation messages after each action
- Personal data is protected, and users can delete their account at any time

## In one sentence

*JobBoard brings job seekers and employers together on one simple, secure platform.*

---

# Symfony Docker

A [Docker](https://www.docker.com/)-based installer and runtime for the [Symfony](https://symfony.com) web framework,
with [FrankenPHP](https://frankenphp.dev) and [Caddy](https://caddyserver.com/) inside!

Coding-agents ready: ships with a [Dev Container](https://containers.dev/) and a [one-page guide](docs/agents.md)
to run [OpenCode](https://opencode.ai), [Claude Code](https://claude.ai/claude-code), or any AI coding assistant,
against a local or a remote model, with an optional network sandbox.

![CI](https://github.com/dunglas/symfony-docker/workflows/CI/badge.svg)

## Getting Started

1. Run `git clone https://github.com/EpitechMscProPromo2029/T-WEB-501-REN-5-1-job_board-1.git` to clone the project.
2. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+).
3. Get yourself set up in the cloned project.
4. Create a file call `.env`, and past in the `.env.example` file. Update database variables environment for more safety.
5. Run `docker compose build --pull --no-cache` to build fresh images.
6. Run `docker compose up --wait` to set up and start the project.
7. Run `docker compose exec php bash`.
8. Inside the PHP container, run `php bin/console doctrine:fixtures:load --env=dev --no-interaction` to load the fixtures in the project. (See [Infos](#infos) if that not work)
9. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334).
10. For check all identifier for each user, connect yourself with the admin account, id: `admin@test.com`, and all user password from fixtures is `123`
11. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Infos

- For step 8, if you are in the developpement environment (`APP_ENV=dev`), run `php bin/console doctrine:fixtures:load` to load the fixtures.

## Features

- Production, development and CI ready
- Just 1 service by default
- Super-readable configuration
- Blazing-fast performance thanks to [the worker mode of FrankenPHP](https://frankenphp.dev/docs/worker/)
- [Installation of extra Docker Compose services](docs/extra-services.md) with Symfony Flex
- Automatic HTTPS (in dev and prod)
- HTTP/3 and [Early Hints](https://symfony.com/blog/new-in-symfony-6-3-early-hints) support
- Real-time messaging thanks to a built-in [Mercure hub](https://symfony.com/doc/current/mercure.html)
- [Vulcain](https://vulcain.rocks) support
- Native [XDebug](docs/xdebug.md) integration
- [Hot Reloading](https://frankenphp.dev/docs/hot-reload/)
- [Dev Container](https://containers.dev/) support
- [AI coding agents](docs/agents.md) with an optional network sandbox
- Rootless, slim production image

**Enjoy!**

## Docs

1. [Options available](docs/options.md)
2. [Using Symfony Docker with an existing project](docs/existing-project.md)
3. [Support for extra services](docs/extra-services.md)
4. [Deploying in production](docs/production.md)
5. [Debugging with Xdebug](docs/xdebug.md)
6. [TLS Certificates](docs/tls.md)
7. [Using MySQL instead of PostgreSQL](docs/mysql.md)
8. [Using Alpine Linux instead of Debian](docs/alpine.md)
9. [Using a Makefile](docs/makefile.md)
10. [Updating the template](docs/updating.md)
11. [Troubleshooting](docs/troubleshooting.md)
12. [Using AI coding agents](docs/agents.md)

## License

Symfony Docker is available under the MIT License.

## Credits

Created by [Kévin Dunglas](https://dunglas.dev), co-maintained by [Maxime Helias](https://twitter.com/maxhelias) and sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
