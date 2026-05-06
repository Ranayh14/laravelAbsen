import pypdf
import sys

def extract_text(pdf_path, output_path):
    try:
        reader = pypdf.PdfReader(pdf_path)
        text = ""
        for page in reader.pages:
            text += page.extract_text() + "\n\n"
        
        with open(output_path, "w", encoding="utf-8") as f:
            f.write(text)
        print(f"Successfully extracted text to {output_path}")
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    pdf_path = r"d:\xampp\htdocs\Magang\Absen\607012300035_Rana Yoda Hanifah_Buku Tugas Akhir FIT Jalur Magang Dua Semester v.0 (1).pdf"
    output_path = r"d:\xampp\htdocs\Magang\Absen\thesis_text.txt"
    extract_text(pdf_path, output_path)
