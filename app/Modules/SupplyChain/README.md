# Modul 8: Supply Chain (LogiFlow SCM)

## Domain Overview
Perencanaan kebutuhan bahan baku & kemas (MRP), penerimaan gudang multi-warehouse, pengelolaan status persediaan (Quarantine, Active, Blocked), pencocokan tagihan pemasok (3-Way Matching), dan pelacakan HPP aktual.

## Schema Data Inti
- `warehouses`
- `goods_receipts`
- `qc_inspections`
- `mrp_runs`
- `vendor_invoices`

## Fitur Utama
1. Material Requirement Planning (MRP) Engine (Net Requirement Formulation)
2. Goods Receipt (GR) & Automatic QC Quarantine Isolation
3. Multi-Warehouse Stock Management (Utama, Karantina, Lab R&D, Transit, FG)
4. Automated 3-Way Matching AP Engine (PO vs GR vs Invoice)
5. Rekonsiliasi Akhir Kalkulasi HPP Riil per Batch
