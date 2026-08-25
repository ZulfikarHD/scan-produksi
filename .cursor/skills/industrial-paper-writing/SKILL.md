---
name: industrial-paper-writing
description: Author high-impact industrial engineering, operational improvement, Kaizen, and technical papers with flowing storytelling, shop-floor grounding, executive takeaways, and verified empirical data. Use when writing, reviewing, or structuring formal technical papers, Kaizen reports, innovation proposals (such as IAKA), operational case studies, or manufacturing whitepapers.
---

# Industrial & Operational Paper Writing

A comprehensive guide for authoring rigorous, narrative-driven technical and operational papers, Kaizen reports, and innovation proposals.

## Core Writing Philosophy

1. **Flowing Industrial Storytelling:**
   - Avoid dry, disjointed bullet-point lists or generic prompt-like AI summaries.
   - Build deep operational context through connected narrative paragraphs that portray shop-floor realities, human workflows, machine operations, and administrative friction.
   - **Strictly Ban Formulaic Antithesis & Rhetorical Grandstanding:** Never use AI clichés like *"bukan sekadar X, melainkan Y"*, *"bukan hanya X biasa, melainkan restrukturisasi fundamental"*, *"mengukir tonggak sejarah baru"*, or *"lompatan paradigma"*. State technical mechanisms, components, and factual workflows directly (*Show, don't preach*).
2. **Shop-Floor Operational Grounding:**
   - Ground every claim in real factory dynamics (fleets, lines, shifts, operator behavior, data silos, maintenance cycles, and customer SLAs).
3. **Executive Clarity & Visual Callouts:**
   - Anchor chapters with structured `Executive Takeaways`.
   - Pair charts and tables with clear, highlighted `Business Insight` and `Key Financial Insight` callout boxes.
4. **Empirical Data Rigor:**
   - Every number must carry **Value, Unit, Period, and Verified Source**.
   - Show transparent mathematical models using LaTeX block equations.

---

## Document Architecture

For comprehensive papers and innovation submissions, organize chapters following this progression:

| Phase | Chapter Focus | Key Deliverable |
|---|---|---|
| **1. Problem & Context** | Background & Problem Identification | Operational profile, *data silo* friction, baseline defect data, 5-Pillar Cost of Inaction |
| **2. Root Cause** | Root Cause Analysis (RCA) | Fishbone (4M/5M), 5-Why tree, core bottleneck |
| **3. Proposed Solution** | Mechanism & Solution Design | Architecture, functional modules, cause-effect mapping |
| **4. Novelty & Workflow** | Novelty & Process Transformation | Capability matrix, Before vs After workflow, updated SOPs |
| **5. MVP Plan** | Trial & Execution Design | Scope, PICs & Facilitators, test matrix, Gantt chart |
| **6. Validation & Results** | Implementation & Empirical Results | Chronological log, obstacles & problem-solving, Before vs After ($n$ samples) |
| **7. Financial Impact** | Financial Model & Value Creation | Open formula, cost avoidance, CAPEX/OPEX, Payback Period |
| **8. Non-Financial Impact** | Quality, People, Customer & ESG | Quality metrics, culture shift, ESG waste reduction, national readiness |
| **9. Sustainability** | Institutionalization & Governance | SOP/IK registrations, system phase-out, knowledge transfer matrix, audit cycle |
| **10. Lessons Learned** | Challenges, Mitigations & Key Takeaways | Cultural/technical hurdles, mitigations, core principles |
| **11. Future Roadmap** | Conclusion & Next Steps | Executive conclusion, short-term scoring, medium-term IoT/maintenance, replication |

---

## Writing Guidelines & Rules

### 1. Opening Chapters with Executive Takeaway
Every chapter must open with a concise blockquote highlighting the core essence:
```markdown
> ***Executive Takeaway:***  
> [2-4 sentences summarizing key operational context, verified baseline metrics, operational blind spots, and verified/projected business outcome]
```

### 2. Pairing Visuals with Business Insights
Never leave charts or key tables unaccompanied. Follow them immediately with dedicated callout blocks:
```markdown
![Grafik Distribusi Baseline](../extracted_images/image1.png)
*Gambar 1.1: Grafik Distribusi Inschiet Cetak per Kuartal 2025 vs Garis Rata-rata Baseline 4,61% (Sumber: Rekap SIRINE & SAP ZPPRSIPPC0012)*

> ***Business Insight Gambar 1.1:***  
> [1-2 sentences highlighting operational implications, anomalies, or turning points shown in the visual]
```

### 3. Verification Standard for Quantitative Data
Adhere strictly to the **4-Attribute Rule** for all figures:
1. **Value (Nilai):** e.g., `177.636.930`, `4,61%`, `Rp 2,23 Miliar`
2. **Unit (Satuan):** e.g., `Lembar Cetak`, `pp`, `Rupiah`, `Jam / Mesin`
3. **Period (Periode):** e.g., `Jan – Mar 2025`, `Semester 1 2026`
4. **Verified Source (Sumber Data):** e.g., `Modul SAP Production Order (T-Code: ZPPRSIPPC0012)`

### 4. Mathematical Modeling Format
Display calculations in open, step-by-step LaTeX equations:
```markdown
$$\begin{aligned}
\text{Total Volume Order Aktual 2025} &= 177.636.930 \text{ Lembar Cetak} \\
\text{Baseline Inschiet (4,61\%)} &= 177.636.930 \times 4,61\% = \mathbf{8.189.062 \text{ Lembar Rusak}} \\
\text{Nilai Kerugian Finansial} &= 8.189.062 \text{ lembar} \times \text{Rp } 3.000 = \mathbf{\text{Rp } 24.567.186.000 \text{ / Tahun}} \\
&\approx \mathbf{\text{Rp } 24,56 \text{ Miliar / Tahun}}
\end{aligned}$$
```

---

## Additional Resources

- For concrete Before/After writing examples and stylistic comparisons, see [examples.md](examples.md).
- For domain frameworks, terminology, and 5-Pillar risk matrices, see [reference.md](reference.md).
