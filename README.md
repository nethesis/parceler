# Space Delivery

Repo management for Nethsecurity installations.

## Development Setup

### Prerequisites

- [Docker](https://docs.docker.com/engine/)
- [Docker Compose](https://docs.docker.com/compose/)

`Podman` and `Podman Compose` can be used as an alternative to Docker and Docker Compose, however no deep testing has been done with these tools.

### Environment

Copy the `.env.example` file to `.env` and edit the entries as needed.

Most of the environment variables are self-explanatory and there's no need to change their defaults unless explicitly told so.
However, there are a few that you might want to change:

- `APP_TIMEZONE`: The timezone to use for the app. Even if the container inherits the host's timezone, it's recommended to set this value to avoid any issues.
- `FILESYSTEM_DISK`: Disk to use for I/O ops (cloning repositories and snapshots), defaults to `local` (storage/app directory). If you want to use a different disk, you need to set the corresponding values for the disk you want to use.
  
  For example, you can directly connect to a DO Space by filling up the AWS_* values with the corresponding values from the DO Space.

  ```env
    FILESYSTEM_DISK=s3
    AWS_ACCESS_KEY_ID=your_access_key
    AWS_SECRET_ACCESS_KEY=your_secret_key
    AWS_DEFAULT_REGION=region of the bucket
    AWS_BUCKET=name of the bucket
    AWS_ENDPOINT=https://<region of the bucket>.digitaloceanspaces.com
  ```
  
  Additional docs can be found in [Laravel Documentation](https://laravel.com/docs/11.x/filesystem).
- `UID`: The user ID for the development environment, set this before running any other command, if this value changes you will need to run the command under [Build images](#build-images) again.
- `GID`: The group ID for the development environment, set this before running any other command, if this value changes you will need to run the command under [Build images](#build-images) again.

### Build images

To build the development images, you just run the following command:

```bash
docker compose build
```

### Setup development environment

Now we just miss a few steps that will need to be run **only once**:

```bash
docker compose run --rm php php artisan key:generate
```

### Running the development server

You're almost there! Run the following command to start up all the needed services:

```bash
docker compose up
```

You can find the app running at `http://localhost:8080`.

### Running commands

To run any commands inside the development environment, you need to get to the shell using:

```bash
docker compose exec --user www-data app bash
```

### Running tests

Software is being tested using PestPHP. To run the tests, you can use the provided command inside the development environment:

```bash
php artisan test
```

## Build

The deployment of the image is being taken care of by GitHub Actions, however if you want to build the production image yourself follow the instructions below.

### Prerequisites

- [Docker Bake](https://docs.docker.com/build/bake/)

To build the production images:

```bash
docker buildx bake
```

You will find the image tagged as `ghcr.io/nethserver/parceler:latest`.

## Run the production image

To run the production image, you can just run the image with some expedients:

- Server runs on port `80`
- Following environment variables are required:
  - `APP_KEY`: The application key, you can generate one using the development environment using `php artisan key:generate --show`.
  - `APP_TIMEZONE`: The timezone to use for the app, defaults to `UTC`.
  - `APP_URL`: The full URL where the application is reached from.
  - `LOG_CHANNEL`: Set this to `errorlog` to avoid writing logs to the filesystem.
  - `LOG_LEVEL`: This can be safely set to `warning`, you can increase the log level if needed.
  - `DB_DATABASE`: This is the name of the database file to use (sqlite), must be an absolute path and wrote inside a volume.
  - `FILESYSTEM_DISK`: Disk to use during production, works same as development.
  - `AWS_ACCESS_KEY_ID`: AWS Access Key ID.
  - `AWS_SECRET_ACCESS_KEY`: AWS Secret Access Key.
  - `AWS_DEFAULT_REGION`: AWS Default Region.
  - `AWS_BUCKET`: AWS Bucket Name.
  - `AWS_ENDPOINT`: AWS Endpoint.
- **optional** to sync all software with the same timezone, the additional `/etc/localtime:/etc/localtime:ro` volume mount can be done to ensure time is respected.
