# Bashkir Folkloric Edition (Электронное научное издание «Фольклорный архив Башкирского государственного университета»)

[![Project Status](https://img.shields.io/badge/status-archival%20%7C%20legacy-yellow.svg)]()

This repository contains the source code and data for the **Electronic Scholarly Edition of the Bashkir Folklore Archive** (Bashkir State University). The edition is accessible online at: [http://nevmenandr.net/pages/bashfolk.php](http://nevmenandr.net/pages/bashfolk.php)

**⚠️ Important Note on Architecture:**  
This project was created in the early 2010s. Unlike modern dynamic web applications, the **wordform index is not generated on-the-fly**. All pages with wordforms were pre-generated via a local processing script. While suboptimal by current standards, this static approach was a practical solution for the time.

## Citation for the Scholarly Description

If you use or refer to this digital edition in academic work, please cite its accompanying scholarly article:

> Галлямов А. А., Орехов Б. В. [Об электронном научном издании «Фольклорный архив Башкирского государственного университета»](./bashfolk_paper.pdf) // Вестник РГГУ. Серия «История. Филология. Культурология. Востоковедение». — 2016. — № 12. — С. 140—149.

## Overview

This digital edition provides access to a corpus of Bashkir folkloric texts. The materials were collected during folklore expeditions organized by Bashkir State University over several decades, starting from the late 1950s. The archive includes texts recorded in the Republic of Bashkortostan and neighboring regions: Perm Krai, Sverdlovsk, Chelyabinsk, Kurgan, Orenburg Oblasts, and Tatarstan.

The edition is designed to:
- **Accumulate** digitized versions of folklore works collected since the 1940s.
- **Provide** free access to the Bashkir State University folklore archives.
- **Enhance** the effectiveness of scholarly research through a multifunctional software environment.
- **Popularize** folk art.

Key principles of the edition:
- **Authenticity:** Texts are published as they were recorded in the archive, without editorial or literary editing (except for obvious errors). Spelling and punctuation of the sources are preserved.
- **First-time publication:** Most of the collected texts are published here for the first time.
- **Metadata-rich:** Each record includes metadata: title, genre, place and year of recording, language, source publication, collector's name, and informant's name.
- **Bilingual interface:** Folklore texts are presented in the original Bashkir; descriptions and interface elements are in Russian.

## Repository Structure

```
bashfolk_edition/
├── efolk/                 # Core folkloric data and associated static pages
├── form_index/            # PRE-GENERATED wordform index (all static HTML pages)
├── snippet/               # Reusable HTML snippets (headers, footers, navigation)
├── bashedition.html       # Main entry point (front page of the edition)
└── bashfolk.php           # Legacy PHP wrapper (minimal, ~0.1% of codebase)
```

### The `form_index/` Directory – Key Component

This directory holds the **pre-generated HTML pages for all wordforms**. Each page corresponds to a specific wordform and lists its occurrences in the corpus with frequency data. Because these pages are static:
- ✅ They load quickly and require no database queries.
- ❌ Adding or correcting texts requires rerunning the entire generation script locally and re-uploading many files.

## Features (as described in the 2016 article)

The electronic edition provides the following functional capabilities:

1. **User access** to information.
2. **Search** for individual works through the edition's database.
3. **Lexical search** using the wordform index.
4. **Frequency dictionary** linked to pages showing contexts of wordform usage.
5. **Information export** (including TEI-conformant XML).
6. **Bibliographic citation** generation according to GOST standards (appears at the bottom of each document).
7. **Data visualization** – distribution of records by genre and geographic region.

### Interactive Services

- **Interactive maps** (powered by Google Maps API) showing the location where each text was recorded.
- **Automatically generated tables and graphs** showing text distribution by recording location and genre.
- **Sortable interactive table of contents** – click on column headers (title, genre, place, year, language, source) to sort in ascending or descending order.
- **Geographic coordinates** associated with each settlement (e.g., Maloyaz: 55.177789, 58.157158; Akchura: 51.696991, 57.514141), enabling future dialectological and folkloric atlases.

### Bibliographic Export

- Citations conform to **GOST 7.0.5-2008**.
- Export to **XML** following **TEI (Text Encoding Initiative)** international consortium standards.

### Genre Abbreviations Used in the Edition

| Abbreviation | Full meaning |
|--------------|---------------|
| песня б | бытовая песня (everyday song) |
| песня л | лирическая песня (lyrical song) |
| сказка б | бытовая сказка (everyday tale) |
| сказка в | волшебная сказка (fairy tale/magical tale) |
| сказка ж | сказка о животных (animal tale) |

## How It Was Built (Historical Context)

Based on the scholarly article, the creation process was:

1. **Original Data:** Folklore texts from the department's archive (145 volumes of typewritten texts and 53 volumes of handwritten texts) were prepared.
2. **Metadata Compilation:** Each text was supplied with metadata: title, genre, place and year of recording, language, source publication, collector and informant names, and archive reference (file and page number).
3. **Local Processing:** A script (not included in this public repository) was run locally to:
   - Tokenize texts.
   - Identify all unique wordforms.
   - Generate a complete HTML page for each wordform (lists of citations, frequencies, etc.).
   - Create cross-linkages between text pages and wordform index pages.
4. **Publication:** The resulting static HTML files (including those in `/form_index`) were uploaded to a web server at `http://lcph.bashedu.ru` (now archived/moved to `nevmenandr.net`).

The original 2016 article notes that the edition is a **dynamic system** – the task of representing the entire folklore fund is not achievable immediately. The starting point was the folklore fund of the Department of Bashkir Literature and Folklore.

## Viewing the Project Locally

Because the project is entirely static HTML/CSS (with minimal PHP), you can run it locally using any simple web server:

```bash
# Using Python 3
python -m http.server 8000

# Using PHP (if you need to test the minimal PHP logic)
php -S localhost:8000
```

Then open your browser to `http://localhost:8000/bashedition.html`.

## Content and Intellectual Property

- **Code:** Provided for archival and scholarly reference.
- **Textual Data:** The Bashkir folkloric texts themselves are published for the first time. No editorial or literary editing was performed – all dialectal lexical, orthoepic, and grammatical features are preserved. For permissions and reuse, please refer to the original online edition or contact the repository owner.

## Research Potential (as envisioned in the article)

The authors ([Gallyamov & Orekhov, 2016](./bashfolk_paper.pdf)) envisioned that this corpus would enable:

- **Corpus linguistics** analysis (extraction of stable epithets, text clichés, collocations).
- **Data mining and information extraction** to identify key concepts of the linguistic and folkloric worldview.
- **Motif extraction** (automated extraction of folkloric motifs, systematized by genre and place of existence).
- **Dialectological atlas** creation by mapping wordforms (including dialectal, archaic, and marked forms) across the Bashkir language area.
- **School education** use – teachers of Bashkir language and literature, local history, and extracurricular activities can use authentic dialectal texts.

## Future Work / Modernization Suggestions

To bring this edition up to current standards, one could:
- Reimplement the wordform index as a dynamic database-driven feature (e.g., using SQLite + PHP/Node.js/Python).
- Create an API to serve the same data.
- Convert the static generation script into a reproducible pipeline (e.g., with `make` or a static site generator like Hugo, Zola, or 11ty) and include it in this repository.
- Update the mapping service (Google Maps API has changed significantly since the early 2010s).

## Citation for the Scholarly Description

- Gallyamov A. A., Orekhov B. V. (2016). "[On the electronic scholarly edition 'Folklore Archive of Bashkir State University'.](./bashfolk_paper.pdf))" *Bulletin of RSUH. Series 'History. Philology. Cultural Studies. Oriental Studies'*, No. 12, pp. 140–149.

## Blogpost

[dev.to](https://dev.to/nevmenandr/from-folklore-to-legacy-code-how-a-2010s-digital-edition-runs-almost-without-a-backend-35im)

## Contact

- **Repository Owner:** Boris Orekhov ([GitHub: nevmenandr](https://github.com/nevmenandr))
- **Live Edition:** [http://nevmenandr.net/pages/bashfolk.php](http://nevmenandr.net/pages/bashfolk.php)
- **Original Lab Page (archived):** `http://lcph.bashedu.ru` (no longer active)


