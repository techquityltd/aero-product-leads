# Aero Product Leads

**Aero Product Leads** is a Laravel package for [AeroCommerce](https://aerocommerce.com) that allows you to collect and manage product-specific leads from customers. Leads are tied to products via tags and routed to the appropriate store location by email. Leads can originate from customer orders or standalone form submissions.

---

## ✨ Features

- Assign product tags to trigger leads.
- Automatically email the nearest or preferred store when a lead is created.
- Compatible with both customer orders and public-facing JS forms.
- Admin interface for filtering, exporting, and viewing leads.
- Google Geolocation support using customer postcode.
- Fallback email handling if no location is matched.

---

## 🛠 Installation

Install the package via Composer:

```bash
composer require techquity/aero-product-leads
```

---

## ⚙️ Migrations

After installation, run the package's migrations:

```bash
php artisan migrate --force
```

> Always re-run migrations after package updates to ensure your schema stays up to date.

---

## 🔧 Admin Settings

You can configure the package in the Aero admin panel:

**Configuration** → **Manage Settings** → **Product Leads**

Settings include:
- **Tag Selector**: Define which product tags should trigger leads.
- **Fallback Email**: Enable and configure a default recipient when no location match is found.

---

## 📬 Lead Emails

Leads are emailed to a location address once created. There are two types:

### 1. **Order-Based Leads**

Triggered by customer orders where tagged products are purchased.

Email includes:
- Customer name, email, phone number
- Order reference and shipping address
- List of matching lead products (name, SKU, quantity)

### 2. **Form-Based Leads**

Submitted via JavaScript forms without a completed order.

Email includes:
- Customer name, email, phone number
- Variant SKU
- Submitted postcode

---

## 📇 Admin Interface

Navigate to **Modules → Product Leads** in Aero Admin.

The interface includes:
- Full table of leads with filters (created date, email sent status, etc.)
- Search by email, SKU, or order reference
- Visual indicators for:
  - Email sent status
  - Location matched
- Bulk export functionality (CSV)

---

## 🧪 JavaScript Form Submission

This package supports lead submissions via JS from anywhere on your frontend.

### Required Fields

The following POST parameters must be submitted:

| Field              | Type     | Required | Description                          |
|-------------------|----------|----------|--------------------------------------|
| `variant_id`       | string   | ✅        | The SKU or ID of the product variant |
| `postcode`         | string   | ✅        | Postcode used for location match     |
| `email`            | string   | ❌        | Customer’s email address             |
| `name`             | string   | ❌        | Customer’s full name                 |
| `telephone`        | string   | ❌        | Customer’s phone number              |

### Example AJAX Submission

```js
fetch('/product-lead', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  },
  body: JSON.stringify({
    variant_id: '12345',
    email: 'john@example.com',
    name: 'John Doe',
    telephone: '07777777777',
    postcode: 'LS1 1AA',
  })
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    alert('Thanks! Your enquiry has been sent.');
  }
});
```

---

## 🧩 Extending the Package

- Customize the admin table (`ProductLeadResourceList`)
- Override mail views (`resources/views/vendor/techquity/aero-product-leads/mail`)
- Add new filters or bulk actions
- Submit to custom endpoints by modifying the controller

---

## ❓ Troubleshooting

- Ensure your products have the correct tag configured in settings.
- Ensure your JS form submits a valid `variant_id`.
- Check that store location emails are saved in the `locations` table.
- Enable fallback email if needed.

---
