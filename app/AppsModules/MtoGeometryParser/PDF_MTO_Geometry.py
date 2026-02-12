#!/usr/bin/env python
# coding: utf-8

# In[7]:


import pdfplumber
import pandas as pd
import os
from datetime import datetime
import warnings
warnings.filterwarnings("ignore")

# Change the current working directory to the script's directory
script_path = os.path.abspath(__file__)
script_directory = os.path.dirname(script_path)
os.chdir(script_directory)

# Directory containing PDFs
pdf_dir = r"truePDFS_w_MTO_Geometry"

# Loop through all PDF files in the directory
for filename in os.listdir(pdf_dir):
    if filename.lower().endswith(".pdf"):
        pdf_path = os.path.join(pdf_dir, filename)
        print(f"Processing: {filename}")
        
        with pdfplumber.open(pdf_path) as pdf:
            total_pages = len(pdf.pages)
            found_tables = []  # To store (page_num, target_df)

            # Search all pages and collect target tables
            for page_num in range(total_pages):
                page = pdf.pages[page_num]
                tables = page.extract_tables()
                for table in tables:
                    if not table or len(table) < 2:
                        continue
                    last_row = table[-1]
                    try:
                        if last_row and last_row[0] and last_row[0].strip().lower() in ["section", "noitces"] and last_row[1] and last_row[1].strip().lower() in ["legs", "sgel", "length (ft)", "length\n(ft)", ")tf(\nhtgnel"]:
                            target_df = pd.DataFrame(table[1:], columns=table[0])
                            found_tables.append((page_num, target_df))
                            break
                    except:
                        pass
            if not found_tables:
                print(f"No matching table found in {filename}. Skipping.")
                continue

            # Decide which table to use
            chosen_page_num, target_df = found_tables[0]

            # Reverse the DataFrame rows
            df = target_df[::-1].reset_index(drop=True)

            # Save old header
            old_header = list(df.columns)

            # Set new header
            df.columns = df.iloc[0]
            df = df.drop(0).reset_index(drop=True)

            # Append old header as last row
            df.loc[len(df)] = old_header

            # Clean up line breaks and reverse cell text
            df = df.applymap(lambda x: x.replace('\n', ' ') if isinstance(x, str) else x)
            df = df.applymap(lambda x: x[::-1] if isinstance(x, str) else x)

            # Reverse column headers too
            df.columns = [col[::-1] if isinstance(col, str) else col for col in df.columns]

            target_col = None
            for col in df.columns:
                cleaned_col = col.replace('\n', ' ').strip() if isinstance(col, str) else col
                if cleaned_col == "# Panels @ (ft)":
                    target_col = col
                    break
            
            if target_col:
                split_df = df[target_col].str.split('@', expand=True)
                df["Panels, Height"] = split_df[0].str.strip()
                df["Panels, Width"] = split_df[1].str.strip() if split_df.shape[1] > 1 else ""
                df = df.drop(columns=[target_col])
                
            # Sort
            df['Section'] = df['Section'].astype(str)
            df['Section_num'] = df['Section'].str.extract(r'T(\d+)').astype(float)
            df_sorted = df.sort_values(by='Section_num').drop(columns='Section_num').reset_index(drop=True)

            # Fill missing values
            df_filled = df_sorted.ffill()

            # Remove non-alphabetic characters from column headers
            df_filled.columns = df_filled.columns.str.replace(r'[^a-zA-Z]', '', regex=True)
            df_filled.columns = df_filled.columns.str.lower()

            # Save to Excel with same base name
            excel_path = os.path.join(pdf_dir, "1_Geometry.xlsx")
            df_filled.to_excel(excel_path, index=False)
            #print(f"Saved to {excel_path}")
            
            # Save to JSON with same base name
            json_path = os.path.join(pdf_dir, "1_Geometry.json")
            df_filled.to_json(json_path, orient='records', indent=4)
            #print(f"Saved to {json_path}")


# In[ ]:




