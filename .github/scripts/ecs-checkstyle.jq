# Turns the JSON report of `ecs check --output-format=json` into checkstyle XML for cs2pr.
#
# ECS 13.3 dropped its own checkstyle formatter (ecsphp/ecs-src#74), which cs2pr relied on to
# annotate pull requests. That formatter only reported one warning per file, without a line.
# This one reads the diff of each fixer run and reports one warning per hunk, placed at the
# hunk's first changed line, plus one error per sniff violation (those come with a line).
#
# Usage: jq -r --arg ws "$PWD/" -f ecs-checkstyle.jq report.json
# `ws` is the workspace prefix stripped from the absolute paths ECS reports.

def esc: tostring | gsub("&"; "&amp;") | gsub("\""; "&quot;") | gsub("<"; "&lt;") | gsub(">"; "&gt;");

# A hunk body ("-12,7 +12,7 @@\n context\n-removed\n+added"): its first changed line in the
# original file, that is the hunk start plus the context lines before the first - or + line.
def first_changed_line:
  (capture("^-(?<start>[0-9]+)").start | tonumber) as $start
  | (split("\n")[1:] | map(.[0:1]) | [indices("-")[0], indices("+")[0]] | map(select(. != null)) | min // 0) as $offset
  | $start + $offset;

"<?xml version=\"1.0\" encoding=\"UTF-8\"?>",
"<checkstyle>",
(.files // {} | to_entries[] |
  "  <file name=\"\(.key | ltrimstr($ws) | esc)\">",
  (.value.errors // [] | .[] |
    "    <error line=\"\(.line)\" severity=\"error\" source=\"\(.source_class | esc)\" message=\"\(.message | esc)\"/>"),
  (.value.diffs // [] | .[] |
    (.applied_checkers | map(split("\\")[-1]) | join(", ")) as $fixers |
    ([.diff | split("\n@@ ")[1:][] | first_changed_line] | if length == 0 then [1] else . end)[] |
    "    <error line=\"\(.)\" severity=\"warning\" source=\"EasyCodingStandard\" message=\"\("Coding style violation, run composer cs:fix (" + $fixers + ")" | esc)\"/>"),
  "  </file>"),
"</checkstyle>"
