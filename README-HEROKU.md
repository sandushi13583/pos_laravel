Heroku deployment notes for this repo
===================================

This repository is a Laravel application prepared to run on Heroku using the official PHP buildpack.

Summary
-------
- `Procfile` and `bin/start` are already Heroku-ready. `bin/start` performs storage symlink and then execs
  `vendor/bin/heroku-php-apache2 public/`.
- `composer.json` targets PHP ^8.0 and contains standard Laravel post-install scripts.

Important security action taken
-------------------------------
- The repository previously contained a committed `.env` file. That file has been removed from the repository to prevent secrets from being stored in Git.

Make sure to rotate any secrets that may have been exposed (DB passwords, API keys, APP_KEY, etc.).

Quick deploy checklist (PowerShell)
----------------------------------
1. Log in to Heroku

```powershell
heroku login
```

2. (If you prefer not to delete `.env` locally) If you previously had `.env` tracked and you instead want to keep a local copy but untrack it from git, run:

```powershell
# Remove .env from the index (stop tracking), keep local file
git rm --cached .env
git commit -m "Remove .env from repository; use Heroku config vars"
```

3. Create Heroku app (or use an existing name)

```powershell
heroku create my-app-name
heroku buildpacks:set heroku/php -a my-app-name
```

4. Generate an `APP_KEY` locally and set it on Heroku

```powershell
# Show generated key and copy it
php artisan key:generate --show

# Set on Heroku (paste the key you copied)
heroku config:set APP_KEY="base64:xxxxxxxxxxxxxxxx" -a my-app-name
heroku config:set APP_ENV=production APP_DEBUG=false APP_URL="https://my-app-name.herokuapp.com" -a my-app-name
```

5. Add a database

Option A - Add a ClearDB MySQL addon (single-node MySQL managed by Heroku):

```powershell
heroku addons:create cleardb:ignite -a my-app-name
```

Option B - Use an external managed DB and set the DB_* config vars manually.

6. Set any other necessary Config Vars (mail, S3, payment keys, etc.)

```powershell
heroku config:set DB_CONNECTION=mysql DB_HOST=... DB_PORT=3306 DB_DATABASE=... DB_USERNAME=... DB_PASSWORD=... -a my-app-name
```

7. Push code to Heroku

```powershell
# Add heroku remote if not present
git remote add heroku https://git.heroku.com/my-app-name.git
git push heroku main
```

8. Run runtime tasks

```powershell
heroku run php artisan migrate --force -a my-app-name
heroku run php artisan storage:link -a my-app-name
```

9. Scale dynos (enable worker if needed)

```powershell
heroku ps:scale web=1 -a my-app-name
# If you want Laravel queues to run on a worker dyno
heroku ps:scale worker=1 -a my-app-name
```

10. Stream logs

```powershell
heroku logs --tail -a my-app-name
```

Notes and troubleshooting
-------------------------
- Heroku filesystem is ephemeral. For persistent uploads use a remote storage service (S3). The app currently creates `storage` -> `public/uploads` symlinks at startup but uploaded files will not persist across dyno restarts unless backed by S3.
- If composer install fails due to missing PHP extensions, check Heroku build logs and consider declaring required extensions in `composer.json` or using a buildpack that provides them.
- If you used ClearDB, you may need to parse the `CLEARDB_DATABASE_URL` into DB_HOST/DB_DATABASE/DB_USERNAME/DB_PASSWORD.

After this change
-----------------
- Because `.env` was removed from the repository, commit and push the README and the removal:

```powershell
git add README-HEROKU.md
git commit -m "Remove .env from repo; add Heroku deployment instructions"
git push origin main
```

If you prefer, instead of deleting `.env` from the repo you can run `git rm --cached .env` locally to stop tracking while keeping a local copy. If `.env` was publicly exposed earlier, rotate all secrets that might have been leaked.

If you want, I can also help:
- create a small `deploy` script,
- modify `composer.json` to declare specific PHP extensions, or
- add an S3 disk configuration and docs for attaching S3 backup/storage.

