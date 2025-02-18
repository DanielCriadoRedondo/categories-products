# 📦 REST API for Products and Categories

This project is a **REST API** built with **Symfony** and **Doctrine ORM** to manage products and categories.

## ✨ Features

✅ **CRUD operations** for **Categories** and **Products**.  
✅ **Validation** for entity properties.  
✅ **Pagination** on the `GET /api/v1/products` endpoint.  
✅ **Functional tests** using PHPUnit.  
✅ **JWT Authentication** for secured modifications.  
✅ **Search Filters** for products *(by category, price, and date range)*.  
✅ **Event Listeners** to log product creation and deletion.  
✅ **Postman Collection** available in `postman/Symfony.json` for API testing.  

---

## 🚀 Requirements

🔹 **PHP** 8.0 or later  
🔹 **Composer**  
🔹 **MySQL** or **PostgreSQL**  
🔹 **Symfony CLI** *(optional but recommended)*  
🔹 **Postman** *(for API testing, optional)*  

---

## 🛠️ Installation

### 1️⃣ Clone the repository

```bash
git clone https://github.com/DanielCriadoRedondo/categories-products.git
cd categories-products
```

### 2️⃣ Install dependencies

```bash
composer install
```

### 3️⃣ Configure the environment variables

Copy the `.env` file and adjust the database configuration:

```bash
cp .env .env.local
```

Set up the database credentials in `.env.local`:

```
DATABASE_URL="mysql://root:root@127.0.0.1:3306/symfony_api"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_secret_passphrase
```

### 4️⃣ Generate the database schema

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5️⃣ Generate JWT keys *(required for authentication)*

```bash
php bin/console lexik:jwt:generate-keypair
```

### 6️⃣ Start the Symfony local server

```bash
symfony server:start
```
**or**  
```bash
php -S 127.0.0.1:8000 -t public
```

---

## 🔥 Running Tests

Run the PHPUnit tests:

```bash
php bin/phpunit
```

---

## 📡 API Endpoints

### 📝 **Authentication**
| Method | Endpoint            | Description               | Auth Required |
|--------|---------------------|---------------------------|--------------|
| `POST` | `/api/login_check`  | Generate JWT Token       | ❌ No |

### 🛍 **Products**
| Method  | Endpoint                     | Description                 | Auth Required |
|---------|------------------------------|-----------------------------|--------------|
| `GET`   | `/api/v1/products`           | List all products           | ❌ No |
| `GET`   | `/api/v1/products/{id}`      | Get a product by ID         | ❌ No |
| `POST`  | `/api/v1/products`           | Create a new product        | ✅ Yes |
| `PUT`   | `/api/v1/products/{id}`      | Update an existing product  | ✅ Yes |
| `DELETE`| `/api/v1/products/{id}`      | Delete a product            | ✅ Yes |

### 🗂 **Categories**
| Method  | Endpoint                      | Description                 | Auth Required |
|---------|-------------------------------|-----------------------------|--------------|
| `GET`   | `/api/v1/categories`          | List all categories         | ❌ No |
| `GET`   | `/api/v1/categories/{id}`     | Get a category by ID        | ❌ No |
| `POST`  | `/api/v1/categories`          | Create a new category       | ✅ Yes |
| `PUT`   | `/api/v1/categories/{id}`     | Update an existing category | ✅ Yes |
| `DELETE`| `/api/v1/categories/{id}`     | Delete a category           | ✅ Yes |

---

## 📌 Example API Requests

### 🔑 **1️⃣ Get JWT Token**
```bash
curl -X POST "http://127.0.0.1:8000/api/login_check" \
     -H "Content-Type: application/json" \
     -d '{"email": "user@example.com", "password": "password"}'
```
📌 **Response:**
```json
{
    "token": "eyJhbGciOiJIUzI1NiIsIn..."
}
```

---

### 🛍 **2️⃣ Get All Products**
```bash
curl -X GET "http://127.0.0.1:8000/api/v1/products"
```
📌 **Response:**
```json
{
    "products": [
        {
            "id": 1,
            "name": "Product 1",
            "description": "Test description",
            "price": 49.99,
            "created_at": "2025-02-18 10:00:00",
            "category": {
                "id": 5,
                "title": "Electronics"
            }
        }
    ],
    "page": 1,
    "limit": 10,
    "total": 1
}
```

---

## 📜 **Postman Collection**
To facilitate API testing, a **Postman collection** is available in the repository.

1️⃣ **Download the collection from GitHub**: 
   📂 `postman/Symfony.json`
2️⃣ **Import it into Postman**: 
   - Open **Postman**.
   - Click **`Import`**.
   - Select the downloaded `.json` file.
   - Click **`Confirm`**.



