#!/usr/bin/env python3
"""
Version bump helper script for espocrm-guard.

The version lives in package.json, which names the extension zip (build/guard-core-<version>.zip), and in
package-lock.json. extension.json carries a version only when one is added to it. The script also inserts the
CHANGELOG.md scaffold for the new version, which the release engineer fills in before tagging.

Usage:
    python .github/scripts/bump_version.py <version>
    make bump-version VERSION=x.y.z

No external dependencies required (stdlib only).
"""

from __future__ import annotations

import json
import re
import sys
from datetime import datetime, timezone
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parent.parent.parent

VERSION_PATTERN = re.compile(r"^\d+\.\d+\.\d+$")


def _update_json_version(path: Path, version: str, label: str, *, required: bool) -> bool:
    """Set the top-level version of a JSON file, keeping its indentation and key order."""
    if not path.exists():
        print(f"  ERROR: Could not find {path}")
        return False

    raw = path.read_text()
    data = json.loads(raw)

    if "version" not in data and not required:
        print(f"  {label}: no version field, skipped")
        return True

    data["version"] = version
    if path.name == "package-lock.json":
        data["packages"][""]["version"] = version

    indent = len(re.match(r"^\{\n( +)", raw).group(1)) if re.match(r"^\{\n( +)", raw) else 2
    path.write_text(json.dumps(data, indent=indent, ensure_ascii=False) + "\n")
    print(f"  {label}: version -> {version}")
    return True


def _insert_changelog_scaffold(path: Path, version: str, label: str) -> bool:
    """Insert a version scaffold block into the changelog file."""
    if not path.exists():
        print(f"  ERROR: Could not find {path}")
        return False

    content = path.read_text()
    today = datetime.now(tz=timezone.utc).strftime("%Y-%m-%d")
    header = f"v{version} ({today})"

    if f"v{version} (" in content:
        print(f"  {label}: v{version} entry already exists")
        return True

    title = f"TITLE (v{version})"
    scaffold = (
        f"{header}\n"
        f"{'-' * len(header)}\n"
        f"\n"
        f"{title}\n"
        f"{'-' * len(title)}\n"
        f"\n"
        f"CONTENT\n"
        f"\n"
        f"___\n"
        f"\n"
    )

    match = re.compile(r"^v\d+\.\d+\.\d+ \(", re.MULTILINE).search(content)
    if match:
        new_content = content[: match.start()] + scaffold + content[match.start():]
    else:
        new_content = content.rstrip() + "\n\n" + scaffold

    path.write_text(new_content)
    print(f"  {label}: added v{version} scaffold")
    return True


def main() -> int:
    if len(sys.argv) != 2:
        print("Usage: bump_version.py <version>")
        print("  version must be in X.Y.Z format")
        return 1

    version = sys.argv[1]

    if not VERSION_PATTERN.match(version):
        print(f"Error: '{version}' is not a valid version. Expected format: X.Y.Z")
        return 1

    print(f"Bumping version to {version}...\n")

    results = [
        _update_json_version(PROJECT_ROOT / "package.json", version, "package.json", required=True),
        _update_json_version(PROJECT_ROOT / "package-lock.json", version, "package-lock.json", required=True),
        _update_json_version(PROJECT_ROOT / "extension.json", version, "extension.json", required=False),
        _insert_changelog_scaffold(PROJECT_ROOT / "CHANGELOG.md", version, "CHANGELOG.md"),
    ]

    print()
    ok = all(results)
    print("Version bump complete." if ok else "Version bump completed with errors.")
    return 0 if ok else 1


if __name__ == "__main__":
    sys.exit(main())
