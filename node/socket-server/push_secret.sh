#!/usr/bin/env bash
# Push secrets to GitHub using gh CLI
# Usage: PUSH_SECRETS_GH_REPO=owner/repo ./push_secret.sh

set -e
REPO=${PUSH_SECRETS_GH_REPO:-}
if [ -z "$REPO" ]; then
  echo "Set PUSH_SECRETS_GH_REPO=owner/repo"
  exit 1
fi

API_KEY=$(node -e "console.log(require('./generate_secret.js')())" 2>/dev/null || true)
# If generate_secret.js doesn't stdout, fallback to crypto via node
if [ -z "$API_KEY" ]; then
  API_KEY=$(node -e "console.log(require('crypto').randomBytes(24).toString('hex'))")
fi
JWT_SECRET=$(node -e "console.log(require('./generate_secret.js')(32))")
REDIS_PASS=$(node generate_secret.js 16)

# gh must be authenticated (gh auth login)
if ! command -v gh >/dev/null 2>&1; then
  echo "gh CLI not available. Please install and authenticate."
  exit 1
fi

  echo "Pushing secrets to $REPO"

  echo "$API_KEY" | gh secret set SOCKET_API_KEY --repo "$REPO" --body -
  echo "$JWT_SECRET" | gh secret set SOCKET_JWT_SECRET --repo "$REPO" --body -
  echo "$REDIS_PASS" | gh secret set SOCKET_REDIS_PASSWORD --repo "$REPO" --body -

  echo "Secrets pushed to $REPO"
