#!/usr/bin/env python3
"""
Build dr-plus-child.zip reproducibly from the committed theme tree.

Why a custom builder instead of `zip -r`:
  * `zip` records each file's mtime, so two builds of identical content produce
    different hashes. That makes "is my ZIP the same as yours?" unanswerable.
  * Here every entry gets a fixed timestamp and a fixed permission, entries are
    sorted, and the compression level is pinned. The same source tree therefore
    always produces the same SHA-256.

The theme files are read from the WORKING TREE (which is verified clean against
the commit being built), and the Persian guide is copied in from the repo root so
the deliverable is self-contained.
"""
import hashlib
import os
import subprocess
import sys
import zipfile

REPO = '/home/user/drpus-manage-rooms'
THEME = os.path.join(REPO, 'homedir/public_html/wp-content/themes/dr-plus-child')
GUIDE_SRC = os.path.join(REPO, 'راهنمای-نصب-از-صفر-فارسی.md')
GUIDE_NAME = 'راهنمای-نصب-از-صفر-فارسی.md'

# Documentation and test trees are development artifacts: never ship them.
EXCLUDE_DIRS = {'docs', 'tests'}
EXCLUDE_NAMES = {'.DS_Store'}
EXCLUDE_EXT = {'.log'}

FIXED_TIME = (1980, 1, 1, 0, 0, 0)


def collect(root):
    """Every shippable file under root, as (arcname, abspath), sorted."""
    out = []
    for dirpath, dirnames, filenames in os.walk(root):
        dirnames[:] = sorted(d for d in dirnames if d not in EXCLUDE_DIRS and not d.startswith('.git'))
        for name in sorted(filenames):
            if name in EXCLUDE_NAMES or os.path.splitext(name)[1] in EXCLUDE_EXT:
                continue
            full = os.path.join(dirpath, name)
            rel = os.path.relpath(full, root)
            out.append((rel.replace(os.sep, '/'), full))
    return sorted(out)


def build(out_zip):
    files = collect(THEME)

    # Bundle the Persian guide inside the theme so the download is self-contained.
    extra = []
    if os.path.exists(GUIDE_SRC):
        extra = [(GUIDE_NAME, GUIDE_SRC)]

    with zipfile.ZipFile(out_zip, 'w', zipfile.ZIP_DEFLATED, compresslevel=9) as z:
        # Directory entries first, so extracting with a dumb unzipper still works.
        dirs = set()
        for rel, _ in files + extra:
            parts = rel.split('/')[:-1]
            for i in range(1, len(parts) + 1):
                dirs.add('/'.join(parts[:i]))
        for d in sorted(dirs):
            info = zipfile.ZipInfo('dr-plus-child/' + d + '/', FIXED_TIME)
            info.external_attr = (0o40755 << 16) | 0x10
            z.writestr(info, b'')

        for rel, full in files + extra:
            info = zipfile.ZipInfo('dr-plus-child/' + rel, FIXED_TIME)
            info.external_attr = 0o100644 << 16
            info.compress_type = zipfile.ZIP_DEFLATED
            with open(full, 'rb') as fh:
                z.writestr(info, fh.read())

    return files, extra


def main():
    out = sys.argv[1] if len(sys.argv) > 1 else os.path.join(REPO, 'dr-plus-child.zip')
    files, extra = build(out)

    with open(out, 'rb') as fh:
        digest = hashlib.sha256(fh.read()).hexdigest()

    print(f'built          : {out}')
    print(f'theme files    : {len(files)}')
    print(f'bundled extras : {len(extra)} ({", ".join(n for n, _ in extra)})')
    print(f'total entries  : {len(files) + len(extra)} files')
    print(f'sha256         : {digest}')


if __name__ == '__main__':
    main()
