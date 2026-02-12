#!/usr/bin/env python
# coding: utf-8

# In[57]:


import os
import fitz
import pandas as pd
import warnings
warnings.filterwarnings("ignore")
from datetime import datetime
import sys
import re

# Change the current working directory to the script's directory
script_path = os.path.abspath(__file__)
script_directory = os.path.dirname(script_path)
os.chdir(script_directory)

# Input/output setup
input_folder = "truePDFS_w_MTO_DAL"
output_folder = os.path.join(input_folder, "Playride_Tables")
os.makedirs(output_folder, exist_ok=True)

# Ask user which table(s) to extract
print("Which table would you like to extract?")
print("1 - Designated Appurtenance Loading")
print("2 - Symbol List")
print("3 - Material Strength")
print("4 - All tables")
choice = "1" # input("Enter your choice (1/2/3/4): ").strip()

# Define parser functions
def parse_appurtenance(lines, pdf_name, page_num):
    qty_list, type_list, carrier_list, elevation_list = [], [], [], []
    flag = False
    elev_index = -1
    section_page = None

    for idx, line in enumerate(lines):
        if "DESIGNED APPURTENANCE LOADING" in line:
            section_page = idx
            flag = True
            continue
        if flag:
            if line == "ELEVATION":
                elev_index = idx
            elif line != "TYPE":
                break

    if flag and elev_index != -1:
        i = elev_index + 1
        current_type = ""
        while i < len(lines):
            line = lines[i].strip()
            if line in ["MATERIAL STRENGTH", "SYMBOL LIST", "TOWER DESIGN NOTES"]:
                break
            try:
                float(line)
                if current_type:
                    qty = 1
                    carrier = ""
                    type_clean = current_type.strip()
                    tokens = type_clean.split()

                    if tokens and re.fullmatch(r"\(\d+\)", tokens[0]):
                        qty = int(tokens[0][1:-1])
                        tokens = tokens[1:]

                    if tokens and re.fullmatch(r"\([^)]+\)", tokens[-1]):
                        carrier = tokens[-1][1:-1]
                        tokens = tokens[:-1]

                    type_clean = " ".join(tokens).strip()
                    qty_list.append(qty)
                    type_list.append(type_clean)
                    carrier_list.append(carrier)
                    elevation_list.append(line)
                    current_type = ""
            except ValueError:
                current_type += " " + line
            i += 1

        if type_list:
            df = pd.DataFrame({
                "Qty": qty_list,
                "TYPE": type_list,
                "CARRIER": carrier_list,
                "ELEVATION": elevation_list
            })
            df.to_excel(os.path.join(output_folder, f"{pdf_name}_Appurtenance.xlsx"), index=False)
            df.to_json(os.path.join(output_folder, f"{pdf_name}_Appurtenance.json"), orient='records', indent=4)
            print(f"✅ Appurtenance table saved for {pdf_name}.")

def parse_material_strength(lines, pdf_name):
    grade_list, fy_list, fu_list = [], [], []
    flag = False
    last_index = -1

    for idx, line in enumerate(lines):
        if line.strip() == "MATERIAL STRENGTH":
            flag = True
            continue
        if flag:
            if line.strip() == "Fu":
                last_index = idx
            elif line.strip() not in ["Fy", "GRADE"]:
                break

    if flag and last_index != -1:
        i = last_index + 1
        while i + 2 < len(lines):
            grade, fy, fu = lines[i].strip(), lines[i+1].strip(), lines[i+2].strip()
            if "TOWER DESIGN NOTES" in grade:
                break
            grade_list.append(grade)
            fy_list.append(fy)
            fu_list.append(fu)
            i += 3

        if grade_list:
            df = pd.DataFrame({
                "GRADE": grade_list,
                "Fy": fy_list,
                "Fu": fu_list
            })
            df.to_excel(os.path.join(output_folder, f"{pdf_name}_MaterialStrength.xlsx"), index=False)
            df.to_json(os.path.join(output_folder, f"{pdf_name}_MaterialStrength.json"), orient='records', indent=4)
            print(f"✅ Material Strength table saved for {pdf_name}.")

def parse_symbol_list(lines, pdf_name):
    mark_list, size_list = [], []
    flag = False
    last_index = -1

    for idx, line in enumerate(lines):
        if line == "SYMBOL LIST":
            flag = True
            continue
        if flag:
            if line == "SIZE":
                last_index = idx
            elif line != "MARK":
                break

    if flag and last_index != -1:
        i = last_index + 1
        while i + 1 < len(lines):
            mark = lines[i]
            size = lines[i + 1]
            if "MATERIAL STRENGTH" in mark or mark == "TOWER DESIGN NOTES":
                break
            mark_list.append(mark)
            size_list.append(size)
            i += 2

        if mark_list:
            df = pd.DataFrame({
                "MARK": mark_list,
                "SIZE": size_list
            })
            df.to_excel(os.path.join(output_folder, f"{pdf_name}_SymbolList.xlsx"), index=False)
            df.to_json(os.path.join(output_folder, f"{pdf_name}_SymbolList.json"), orient='records', indent=4)
            print(f"✅ Symbol List table saved for {pdf_name}.")

# Begin PDF loop
for filename in os.listdir(input_folder):
    if not filename.lower().endswith(".pdf"):
        continue

    pdf_path = os.path.join(input_folder, filename)
    pdf_name = os.path.splitext(filename)[0]
    doc = fitz.open(pdf_path)

    if choice != "4":
        print(f"\n📄 Processing: {filename}")

    table_found_pages = {"1": [], "2": [], "3": []}

    for i, page in enumerate(doc):
        text = page.get_text()
        lines = [line.strip() for line in text.split('\n') if line.strip()]

        if choice in ["1", "4"] and any("DESIGNED APPURTENANCE LOADING" in line for line in lines):
            table_found_pages["1"].append(i)

        if choice in ["2", "4"] and any("SYMBOL LIST" in line for line in lines):
            table_found_pages["2"].append(i)

        if choice in ["3", "4"] and any("MATERIAL STRENGTH" in line for line in lines):
            table_found_pages["3"].append(i)

    for table_id in ["1", "2", "3"]:
        if choice == table_id or choice == "4":
            pages = table_found_pages[table_id]
            if not pages:
                continue
            elif len(pages) > 1:
                print(f"⚠️  Multiple pages found for table {table_id} in {filename}: {pages}")
                user_page = 1 # input(f"Which page number do you want to parse for table {table_id}? ").strip()
                try:
                    page_num = int(user_page)
                    text = doc[page_num].get_text()
                    lines = [line.strip() for line in text.split('\n') if line.strip()]
                except Exception:
                    print(f"❌ Invalid page. Skipping {filename}")
                    continue
            else:
                page_num = pages[0]
                text = doc[page_num].get_text()
                lines = [line.strip() for line in text.split('\n') if line.strip()]

            if table_id == "1":
                parse_appurtenance(lines, pdf_name, page_num)
            elif table_id == "2":
                parse_symbol_list(lines, pdf_name)
            elif table_id == "3":
                parse_material_strength(lines, pdf_name)

print("\n✅ All selected tables processed.")

