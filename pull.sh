#!/bin/bash

set -e

echo "⬇️ Änderungen von GitHub holen..."

git pull --rebase origin main

echo ""
echo "✅ Pull erfolgreich."
