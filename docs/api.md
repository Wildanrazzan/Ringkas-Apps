# Ringkas API Documentation

Dokumentasi ini menjelaskan endpoint API yang tersedia pada sistem Ringkas.

## Base URL

```text
/api
```

## Authentication

Sebagian besar endpoint menggunakan `auth:sanctum`.

Gunakan header berikut:

```http
Authorization: Bearer <access_token>
Accept: application/json
Content-Type: application/json
```

Token didapat dari endpoint `register` atau `login`.

## Format Response

Beberapa endpoint sukses memakai wrapper `MessageResource` dengan format:

```json
{
  "status": "200",
  "massage": "Data berhasil diambil",
  "data": {}
}
```

Catatan: field pesan saat ini bernama `massage` mengikuti implementasi yang ada di kode.

---

## Auth

### Register

`POST /api/register`

Request:

```json
{
  "name": "Budi",
  "email": "budi@mail.com",
  "password": "password123"
}
```

Response sukses:

```json
{
  "status": "success",
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "name": "Budi",
    "email": "budi@mail.com"
  },
  "access_token": "1|xxxxx"
}
```

### Login

`POST /api/login`

Request:

```json
{
  "email": "budi@mail.com",
  "password": "password123"
}
```

Response sukses:

```json
{
  "status": "success",
  "message": "User logged in successfully",
  "data": {
    "id": 1,
    "name": "Budi",
    "email": "budi@mail.com"
  },
  "access_token": "1|xxxxx"
}
```

---

## Users

### Get All Users

`GET /api/users`

Protected: ya

Response: daftar user.

---

## Dompet

### List Dompet

`GET /api/dompet`

Protected: ya

Response sukses:

```json
{
  "status": "200",
  "massage": "Data dompet berhasil diambil",
  "data": {
    "dompets": [],
    "total_current_balance": 0
  }
}
```

### Create Dompet

`POST /api/dompet`

Request:

```json
{
  "name": "Cash",
  "currency": "IDR",
  "initial_balance": 100000,
  "is_active": true
}
```

### Update Dompet

`PUT /api/dompet/{id}`

Field opsional:

```json
{
  "name": "Cash Baru",
  "currency": "IDR",
  "initial_balance": 250000,
  "is_active": true
}
```

### Delete Dompet

`DELETE /api/dompet/{id}`

---

## Kategori

### List Kategori

`GET /api/kategori`

Protected: ya

Response sukses:

```json
{
  "status": "200",
  "massage": "Data kategori berhasil diambil",
  "data": []
}
```

### Create Kategori

`POST /api/kategori`

Request:

```json
{
  "name": "Makan",
  "kind": "expense",
  "icon": "utensils",
  "color": "#FFAA00"
}
```

Rules:

- `kind` harus `income` atau `expense`
- `color` harus format hex `#RRGGBB`

### Update Kategori

`PUT /api/kategori/{id}`

### Delete Kategori

`DELETE /api/kategori/{id}`

---

## Transaksi

### List Transaksi

`GET /api/transaksi`

Protected: ya

Query parameter yang didukung:

- `start_date=YYYY-MM-DD`
- `end_date=YYYY-MM-DD`
- `search=kata_kunci`
- `dompet_id=1,2,3`
- `category_id=1,2,3`

Response sukses:

```json
{
  "status": "200",
  "massage": "Data transaksi berhasil diambil",
  "data": {
    "transaksi": [],
    "saldo_awal": 0,
    "saldo_akhir": 0,
    "total_transaksi": 0
  }
}
```

### Create Transaksi

`POST /api/transaksi`

Request:

```json
{
  "dompet_id": 1,
  "category_id": 2,
  "trx_date": "2026-06-20",
  "amount": 25000,
  "note": "Makan siang"
}
```

Catatan:

- `amount` akan dibuat negatif jika kategori bertipe `expense`
- `dompet_id` harus milik user yang sedang login

### Detail Transaksi

`GET /api/transaksi/{id}`

### Update Transaksi

`PUT /api/transaksi/{id}`

Field opsional:

```json
{
  "dompet_id": 1,
  "category_id": 2,
  "trx_date": "2026-06-21",
  "amount": 50000,
  "note": "Update catatan"
}
```

### Delete Transaksi

`DELETE /api/transaksi/{id}`

---

## Status Code Umum

- `200` sukses
- `201` data berhasil dibuat
- `401` token tidak valid / belum login
- `403` tidak punya izin akses data
- `404` data tidak ditemukan
- `422` validasi gagal

## Contoh Alur Penggunaan

1. Register atau login.
2. Simpan `access_token`.
3. Kirim request ke endpoint protected dengan header `Authorization: Bearer <token>`.
4. Buat kategori dan dompet.
5. Tambahkan transaksi.
