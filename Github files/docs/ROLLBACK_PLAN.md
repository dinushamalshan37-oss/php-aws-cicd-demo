# Rollback Plan — GitHub Actions / EC2 demo

1. Before deploying, record the current commit and back up database and uploaded files. Do not store `.env` or secrets in Git.
2. If the new release fails, identify the last known-good commit (on EC2: `sudo -u deploy git -C /var/www/myapp log -5 --oneline`).
3. For an emergency source-only rollback: `sudo -u deploy git -C /var/www/myapp reset --hard PREVIOUS_COMMIT_SHA` (replace the placeholder). **WARNING:** Any new push to `main` will redeploy origin/main, overriding this emergency reset.
4. For a durable rollback, create and merge a properly reviewed revert commit on the GitHub `main` branch, or update the release strategy. Validate the website and check Apache logs.
5. Source-code-only rollback does not reverse database schema migrations or restore user-uploaded files. Restore each from separately verified backups when needed.
6. Avoid destructive failure tests on a real production server. Design database backup, health checks, and migrations before deploying a critical service.
