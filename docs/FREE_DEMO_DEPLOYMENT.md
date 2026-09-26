# Free portfolio demo deployment

The repository includes a Docker image and Render Blueprint for a public demonstration. This setup is for fictional portfolio data, not paying customers.

## Architecture

- Render free web service runs the complete Laravel/Blade application.
- Neon PostgreSQL stores the demo database outside Render's ephemeral filesystem.
- The web container runs the HTTP server, database queue worker and Laravel scheduler while the free instance is awake.
- Live AI is disabled, mail is written to application logs and payments use fictional manual references.

## Deploy

1. Create a Neon project and copy its pooled PostgreSQL connection string.
2. In Render, create a Blueprint from this repository. Render reads `render.yaml`.
3. Enter the Neon connection string for the secret `DB_URL` value.
4. Create the service and wait for `/up` to become healthy.
5. If Render assigns a different hostname, update `APP_URL` to the actual HTTPS URL and redeploy.
6. Sign in with the demo credentials displayed on the login page.

Migrations and the idempotent fictional dataset run when the container starts. `DEMO_MODE=true` must never be used for a real customer deployment.

## Free-tier limits

Render's free web service sleeps when idle, has an ephemeral filesystem and does not provide production availability. Uploaded files can disappear after restarts. The bundled worker and scheduler stop whenever the service sleeps. Use paid compute, object storage, managed email, monitoring and independent backups before serving customers.
