#!/usr/bin/env python
# -*- coding: utf-8 -*-
from uuid import uuid4
import sys
import re


pat = re.compile("([0-9]+)\n---")


if __name__=='__main__':
    with open(sys.argv[1] if len(sys.argv) > 1 else "pivot_hugomd.txt", "r") as f:
        content = f.read()
    entries = content.split("\n--------\n")
    for entry in entries:
        entry = entry.strip()
        if not entry:
            continue
        m = pat.findall(entry)
        if m:
            code = m[0]
            entry = entry[len(code)+1:]
        else:
            code = uuid4().hex
        if entry[-7:]=='\n\n-----':
            entry = entry[:-7]
        with open(code + ".md", "a") as f:
            f.write(entry)

