# Modul 4: Procurement

## Domain Overview
Manajemen rantai pengadaan barang/jasa operasional dan non-operasional, evaluasi pemasok (AVL), kontrol anggaran real-time, serta penerbitan Purchase Order (PO).

## Schema Data Inti
- `purchase_requests`
- `pr_items`
- `purchase_orders`
- `po_items`

## Fitur Utama
1. Granular 8-Step Item Workflow (per-item approval)
2. Bulk Revision & Multi-Supplier Bidding (Quotation Matrix)
3. PR Bon Sementara (Urgent Purchase) dengan Quick Approval GM
4. Integrasi Odoo PO (JSON-RPC + Audit Bypass)
5. Evaluasi AVL (Approved Vendor List) berbasis 4 Sasaran Mutu Pengadaan
