#!/usr/bin/env python

import fitz
# https://pymupdf.readthedocs.io/en/latest/textpage/

doc = fitz.open(r"Hello.pdf")

page_number = doc.pageCount
page = doc[0]

for p in range(page_number):
    print(doc[p].getText("text"))

