#!/usr/bin/env bash
# Remind Claude to use graphify before grepping/searching raw files.
# Fires only when graph.json exists — no-op otherwise.

[ -f graphify-out/graph.json ] || exit 0

CMD=$(python3 -c "import json,sys; d=json.load(sys.stdin); print(d.get('tool_input',d).get('command',''))" 2>/dev/null || true)

case "$CMD" in
  *grep*|*"rg "*|*ripgrep*|*"find "*|*"fd "*|*ack*|*" ag "*)
    echo '{"hookSpecificOutput":{"hookEventName":"PreToolUse","additionalContext":"MANDATORY: graphify-out/graph.json exists. You MUST run `graphify query \"<question>\"` before grepping raw files. Only grep after graphify has oriented you, or to modify/debug specific lines."}}'
    ;;
esac
