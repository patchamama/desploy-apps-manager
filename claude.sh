#!/bin/bash

# Install Claude CLI
curl -fsSL https://claude.ai/install.sh | bash

# Add Claude to PATH
echo 'export PATH="$HOME/.local/bin:$PATH"' >> ~/.bashrc && source ~/.bashrc

# ============================================================
# Configuration: Prevent Claude references in commits
# ============================================================

# Opción 1: Configure Claude Code to NOT include co-authors
echo "Setting up Claude Code configuration..."
mkdir -p ~/.config/claude

cat > ~/.config/claude/config.json << 'CLAUDE_CONFIG'
{
  "commits": {
    "autoCommit": false,
    "includeCoAuthor": false,
    "signCommits": false
  },
  "user": {
    "displayName": "patchamama"
  },
  "markdown": {
    "noEmojis": true,
    "excludeEmojisFromDocumentation": true
  }
}
CLAUDE_CONFIG

echo "✓ Claude Code configured to exclude co-authors"

# Opción 4: Setup global pre-commit hook to filter Claude references
echo "Setting up git pre-commit hook..."
mkdir -p ~/.git-hooks

cat > ~/.git-hooks/prepare-commit-msg << 'GIT_HOOK'
#!/bin/bash
# Filter out any Claude references from commit messages
sed -i '/Co-Authored-By: Claude/d' "$1"
sed -i '/Generated with \[Claude Code\]/d' "$1"
GIT_HOOK

chmod +x ~/.git-hooks/prepare-commit-msg

# Configure git to use the global hooks directory
git config --global core.hooksPath ~/.git-hooks

echo "✓ Git pre-commit hook configured"

# Opción B: Create project-level configuration file
echo ""
echo "Setting up project-level Claude configuration..."

# Get the current project directory (where this script is located)
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CLAUDE_CONFIG_DIR="$PROJECT_DIR/.claude"
CLAUDE_SETTINGS="$CLAUDE_CONFIG_DIR/settings.json"

if [ -f "$CLAUDE_SETTINGS" ]; then
    echo "⚠ Project configuration already exists at: $CLAUDE_SETTINGS"
    echo "Skipping creation to preserve existing settings."
else
    mkdir -p "$CLAUDE_CONFIG_DIR"

    cat > "$CLAUDE_SETTINGS" << 'PROJECT_CONFIG'
{
  "comments": [
    "Este proyecto NO desea referencias a Claude en commits",
    "No incluir 'Co-Authored-By' ni 'Generated with Claude Code'",
    "No incluir emojis en archivos markdown"
  ],
  "restrictions": {
    "noClaudeInCommits": true,
    "noAutoCommits": true,
    "noCoAuthors": true,
    "noEmojisInMarkdown": true
  },
  "markdown": {
    "noEmojis": true,
    "excludeEmojisFromDocumentation": true,
    "excludeEmojisFromREADME": true
  }
}
PROJECT_CONFIG

    echo "✓ Project-level configuration created at: $CLAUDE_SETTINGS"
fi

echo ""
echo "======================================================"
echo "Configuration complete!"
echo "======================================================"
echo ""
echo "✓ Claude Code configured (global)"
echo "✓ Git pre-commit hook configured (global)"
echo "✓ Project settings configured (local)"
echo ""
echo "Claude WILL NOT add co-author references to commits."
echo ""

# Start Claude CLI
claude