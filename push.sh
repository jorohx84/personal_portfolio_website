#!/bin/bash

set -e

echo "🔄 Git Status:"
git status

echo ""
read -p "Commit message: " MESSAGE

if [ -z "$MESSAGE" ]; then
    MESSAGE="Update portfolio"
fi

echo ""
echo "📦 Dateien hinzufügen..."
git add .

echo "💾 Commit erstellen..."
git commit -m "$MESSAGE" || {
    echo "ℹ️ Keine neuen Änderungen zum Committen."
}

echo "⬆️ Push nach GitHub..."
git push origin main

echo ""
echo "✅ Push erfolgreich."
