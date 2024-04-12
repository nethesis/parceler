# Space Delivery

Repo management for Nethsecurity installations.

## Development Setup

### Prerequisites

- [Docker](https://www.docker.com/)

### Environment

Copy the `.env.example` file to `.env` and edit the entries as needed.

Most of the environment variables are self-explanatory, but here are the most edited ones:

- `APP_TIMEZONE`: The timezone to use for the app. This is used mostly for logging.
- `UID`: The user ID for the development environment, set this before running any other command, if this value changes you will need to run the command under [Build images](#build-images) again.
- `GID`: The group ID for the development environment, set this before running any other command, if this value changes you will need to run the command under [Build images](#build-images) again.

### Build images

To build the images, you just run the following command:

```bash
docker compose build
```

### Setup development environment

Now we just miss a few steps that will need to be run only once.

```bash
docker compose run --rm php php artisan key:generate
```

### Running the development server

You're almost there! Run the following command to start up all the needed services:

```bash
docker compose up
```

You can find the app running at `http://localhost`.

### Running commands

To run any commands regarding php, you need to run them inside the container, for example:

```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed
docker compose exec php composer require very/cool-package
```

Or alternatively, you can just shell in the container:

```bash
docker compose exec php bash
```
