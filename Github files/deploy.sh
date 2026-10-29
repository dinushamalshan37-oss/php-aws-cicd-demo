#!/bin/bash
# Server-side deployment script (install as /usr/local/bin/deploy.sh, owned by root:deploy).
# Edit REPO_URL to exactly match: git -C /var/www/myapp remote get-url origin
set -Eeuo pipefail

APP_DIR="/var/www/myapp"
BRANCH="main"
REPO_URL="git@github.com:YOUR_GITHUB_USERNAME/YOUR_REPO.git"
LOCK_FILE="/home/deploy/myapp-deploy.lock"

exec 9>"$LOCK_FILE"
if ! flock -n 9; then
    echo "Another deployment is already running."
    exit 1
fi

echo "Starting deployment: $(date -Is)"

if ! git -C "$APP_DIR" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    echo "ERROR: Application directory is not a Git repository."
    exit 1
fi

CURRENT_URL=$(git -C "$APP_DIR" remote get-url origin)
if [[ "$CURRENT_URL" != "$REPO_URL" ]]; then
    echo "ERROR: Repository URL does not match expected URL."
    exit 1
fi

git -C "$APP_DIR" fetch origin "$BRANCH"
git -C "$APP_DIR" checkout "$BRANCH"
git -C "$APP_DIR" reset --hard "origin/$BRANCH"

DEPLOYED_COMMIT=$(git -C "$APP_DIR" rev-parse --short HEAD)
echo "Deployed commit: $DEPLOYED_COMMIT"

if [[ -f "$APP_DIR/index.php" ]]; then
    php -l "$APP_DIR/index.php"
fi

echo "Deployment completed successfully: $(date -Is)"
