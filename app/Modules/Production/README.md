# Modul 5: Produksi & Manufaktur

## Domain Overview
Perencanaan jadwal produksi mingguan (WPS), eksekusi tahapan batch pabrik herbal/farmasi, pencatatan waktu operasi real-time, pengukuran efisiensi mesin (OEE), dan penanganan produk reject/scrap.

## Schema Data Inti
- `production_schedules`
- `production_batches`
- `batch_downtimes`
- `batch_outputs`

## Fitur Utama
1. Weekly Production Schedule (WPS) kapasitas lini & kesiapan bahan
2. Estafet Batch Tracking System: Mixing -> Filling -> Packing -> Packing Hold
3. Live Active Batch Timer & Downtime Lock SPV
4. Kalkulasi OEE Real-Time (Availability, Performance, Quality Rate)
5. Manajemen Reject & Integrasi Odoo Scrap
