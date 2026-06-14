#!/usr/bin/env bash
# Remind Claude to use graphify before reading source files.
# Fires only when graph.json exists — no-op otherwise.
# Scoped to PHP project source extensions; excludes vendor/, graphify-out/, and .claude/.

[ -f graphify-out/graph.json ] || exit 0

HIT=$(python3 -c "
import json,sys
d=json.load(sys.stdin)
t=d.get('tool_input',d)
s=(str(t.get('file_path') or '')+' '+str(t.get('pattern') or '')+' '+str(t.get('path') or '')).lower().replace(chr(92),'/')
if 'vendor/' in s or 'graphify-out/' in s or '.claude/' in s:
    sys.stdout.write('')
else:
    exts=('.php','.json','.yaml','.yml')
    sys.stdout.write('1' if any(e in s for e in exts) else '')
" 2>/dev/null || true)

if [ "$HIT" = 1 ]; then
  echo '{"hookSpecificOutput":{"hookEventName":"PreToolUse","additionalContext":"MANDATORY: graphify-out/graph.json exists. You MUST run graphify before reading source files. Use: `graphify query \"<question>\"` (scoped subgraph), `graphify explain \"<concept>\"`, or `graphify path \"<A>\" \"<B>\"`. Only read raw files after graphify has oriented you, or to modify/debug specific lines. This rule applies to subagents too — include it in every subagent prompt involving code exploration."}}'
fi
