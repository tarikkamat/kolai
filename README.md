# Kolai Plugin

Kolai API entegrasyonu icin WordPress plugin'i.

## API Genel

Base URL: `https://your-site.com/wp-json/kolai/v1`

Tumu JSON request/response kullanir.

### Base Response

Tum endpoint'ler asagidaki formatta doner:

```json
{
  "status": "success",
  "systemTime": "2026-02-04T10:15:30+00:00",
  "errorCode": null,
  "errorMessage": null,
  "woocommerceVersion": "9.1.0",
  "wordpressVersion": "6.5.3",
  "phpVersion": "8.1.20",
  "data": {}
}
```

`status` degeri:
- `success`: HTTP status < 400
- `failure`: HTTP status >= 400

### Error Codes

#### 1xxx - Kolai Plugin Errors
- `1000` Internal error
- `1001` Bad request
- `1002` Not found
- `1003` Service unavailable
- `1004` WooCommerce inactive

#### 2xxx - Product Errors
- `2000` Invalid product id
- `2001` Product not found
- `2002` Product not visible
- `2003` Variation parent not found
- `2004` Invalid product list

#### 3xxx - Shipping Errors
- `3000` Invalid address
- `3001` No shipping options

## Endpoints

### GET /products

Tum urunleri listeler.

#### Request

```
GET /wp-json/kolai/v1/products
```

#### Response (success)

```json
{
  "status": "success",
  "systemTime": "2026-02-04T10:15:30+00:00",
  "errorCode": null,
  "errorMessage": null,
  "woocommerceVersion": "9.1.0",
  "wordpressVersion": "6.5.3",
  "phpVersion": "8.1.20",
  "data": [
    {
      "id": 12,
      "name": "T-Shirt",
      "slug": "t-shirt",
      "type": "simple",
      "status": "publish",
      "featured": false,
      "catalog_visibility": "visible",
      "description": "...",
      "short_description": "...",
      "sku": "TS-001",
      "menu_order": 0,
      "virtual": false,
      "permalink": "https://your-site.com/product/t-shirt",
      "date_created": "2026-01-12T09:12:00+00:00",
      "date_modified": "2026-01-15T09:12:00+00:00",
      "price": 100,
      "regular_price": 120,
      "sale_price": 100,
      "date_on_sale_from": null,
      "date_on_sale_to": null,
      "total_sales": 5,
      "tax_status": "taxable",
      "tax_class": "",
      "manage_stock": false,
      "stock_quantity": null,
      "stock_status": "instock",
      "backorders": "no",
      "sold_individually": false,
      "purchase_note": "",
      "shipping_class_id": 0,
      "in_stock": true,
      "weight": 0.5,
      "dimensions": { "length": 10, "width": 20, "height": 2 },
      "upsell_ids": [],
      "cross_sell_ids": [],
      "parent_id": 0,
      "attributes": [],
      "default_attributes": [],
      "variations": [],
      "categories": [],
      "tags": [],
      "downloadable": false,
      "downloads": [],
      "download_limit": -1,
      "download_expiry": -1,
      "image": { "id": 1, "url": "https://...", "alt": "" },
      "gallery": [],
      "reviews_allowed": true,
      "rating_counts": [],
      "average_rating": "0",
      "review_count": 0
    }
  ]
}
```

### GET /products/{id}

Tek urun getirir. Var olan bir urun degilse hata doner.

#### Request

```
GET /wp-json/kolai/v1/products/12
```

#### Response (success)

```json
{
  "status": "success",
  "systemTime": "2026-02-04T10:15:30+00:00",
  "errorCode": null,
  "errorMessage": null,
  "woocommerceVersion": "9.1.0",
  "wordpressVersion": "6.5.3",
  "phpVersion": "8.1.20",
  "data": {
    "id": 12,
    "name": "T-Shirt",
    "slug": "t-shirt",
    "type": "simple",
    "status": "publish",
    "featured": false,
    "catalog_visibility": "visible",
    "description": "...",
    "short_description": "...",
    "sku": "TS-001",
    "menu_order": 0,
    "virtual": false,
    "permalink": "https://your-site.com/product/t-shirt",
    "date_created": "2026-01-12T09:12:00+00:00",
    "date_modified": "2026-01-15T09:12:00+00:00",
    "price": 100,
    "regular_price": 120,
    "sale_price": 100,
    "date_on_sale_from": null,
    "date_on_sale_to": null,
    "total_sales": 5,
    "tax_status": "taxable",
    "tax_class": "",
    "manage_stock": false,
    "stock_quantity": null,
    "stock_status": "instock",
    "backorders": "no",
    "sold_individually": false,
    "purchase_note": "",
    "shipping_class_id": 0,
    "in_stock": true,
    "weight": 0.5,
    "dimensions": { "length": 10, "width": 20, "height": 2 },
    "upsell_ids": [],
    "cross_sell_ids": [],
    "parent_id": 0,
    "attributes": [],
    "default_attributes": [],
    "variations": [],
    "categories": [],
    "tags": [],
    "downloadable": false,
    "downloads": [],
    "download_limit": -1,
    "download_expiry": -1,
    "image": { "id": 1, "url": "https://...", "alt": "" },
    "gallery": [],
    "reviews_allowed": true,
    "rating_counts": [],
    "average_rating": "0",
    "review_count": 0
  }
}
```

#### Response (error example)

```json
{
  "status": "failure",
  "systemTime": "2026-02-04T10:15:30+00:00",
  "errorCode": "2001",
  "errorMessage": "Product not found",
  "woocommerceVersion": "9.1.0",
  "wordpressVersion": "6.5.3",
  "phpVersion": "8.1.20",
  "data": null
}
```

### POST /shipment-options

Alias: `POST /shipping-options`

Urun listesine ve adrese gore uygun kargo seceneklerini ve fiyatlarini doner.

#### Request

```
POST /wp-json/kolai/v1/shipment-options
```

```json
{
  "products": [12, 34, 56],
  "address": {
    "countryId": "TR",
    "cityId": "34",
    "districtId": "Kadikoy",
    "postcode": "34710",
    "addressLine": "Ornek Mah. 1. Sok. No: 2"
  }
}
```

Adres alanlari WooCommerce tarafinda su sekilde map edilir:
- `countryId` -> `country`
- `cityId` -> `state` (il/province). TR icin `34` gibi numeric degerler otomatik `TR34` olarak normalize edilir.
- `districtId` -> `city` (ilce)

#### Response (success)

```json
{
  "status": "success",
  "systemTime": "2026-02-04T10:15:30+00:00",
  "errorCode": null,
  "errorMessage": null,
  "woocommerceVersion": "9.1.0",
  "wordpressVersion": "6.5.3",
  "phpVersion": "8.1.20",
  "data": {
    "options": [
      {
        "id": "flat_rate:1",
        "label": "Flat Rate",
        "methodId": "flat_rate",
        "cost": 10,
        "tax": 1.8,
        "price": 11.8
      }
    ]
  }
}
```

#### Response (no shipping options)

```json
{
  "status": "failure",
  "systemTime": "2026-02-04T10:15:30+00:00",
  "errorCode": "3001",
  "errorMessage": "No shipping options available",
  "woocommerceVersion": "9.1.0",
  "wordpressVersion": "6.5.3",
  "phpVersion": "8.1.20",
  "data": null
}
```

#### Response (invalid address)

```json
{
  "status": "failure",
  "systemTime": "2026-02-04T10:15:30+00:00",
  "errorCode": "3000",
  "errorMessage": "countryId, cityId and districtId are required",
  "woocommerceVersion": "9.1.0",
  "wordpressVersion": "6.5.3",
  "phpVersion": "8.1.20",
  "data": null
}
```

#### Response (invalid product list)

```json
{
  "status": "failure",
  "systemTime": "2026-02-04T10:15:30+00:00",
  "errorCode": "2004",
  "errorMessage": "Products list is required",
  "woocommerceVersion": "9.1.0",
  "wordpressVersion": "6.5.3",
  "phpVersion": "8.1.20",
  "data": null
}
```

#### Response (WooCommerce inactive)

```json
{
  "status": "failure",
  "systemTime": "2026-02-04T10:15:30+00:00",
  "errorCode": "1004",
  "errorMessage": "WooCommerce is not active",
  "woocommerceVersion": null,
  "wordpressVersion": "6.5.3",
  "phpVersion": "8.1.20",
  "data": null
}
```

## Yapı

```
kolai/
├── admin/
│   ├── class-kolai-admin.php
│   ├── class-kolai-settings.php
│   ├── css/
│   │   └── kolai-admin.css
│   ├── js/
│   │   └── kolai-admin.js
│   └── views/
│       └── settings-page.php
├── includes/
│   ├── class-kolai-activator.php
│   ├── class-kolai-api.php
│   ├── class-kolai-core.php
│   ├── class-kolai-deactivator.php
│   ├── class-kolai-exceptions.php
│   ├── class-kolai-constants.php
│   ├── class-kolai-loader.php
│   ├── class-kolai-response.php
│   ├── class-kolai-route-base.php
│   ├── product/
│   │   ├── product-mapper.php
│   │   ├── product-routes.php
│   │   └── product-service.php
│   └── shipping/
│       ├── shipping-routes.php
│       └── shipping-service.php
└── kolai.php
```
