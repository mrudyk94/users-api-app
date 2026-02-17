# 🚀 Users API App

Backend API сервіс для роботи з користувачами.  
Проєкт запускається в Docker та використовує Symfony + MariaDB.

---

## 📦 Вимоги

- 🐳 Docker
- 🐳 Docker Compose
- 🐧 Linux / macOS (або WSL для Windows)

## ⚙️ Швидкий старт

### 1️⃣ Клонування репозиторію
```bash
git clone https://github.com/mrudyk94/users-api-app.git
cd users-api-app
```

### 2️⃣ Створення .env
```bash
cp env.dist .env
```

### 3️⃣ Збірка та запуск Docker
```text
# Збірка Docker контейнерів
docker compose build --no-cache

# Підняття контейнерів у фоновому режимі
docker compose up -d
```

В проєкті також є скрипт `run.sh` для зручної роботи з контейнерами.
```text
# Збірка Docker контейнерів
run build

# Підняття контейнерів у фоновому режимі
run up
```

### 4️⃣ Встановлення залежностей (Composer) без входу в контейнер
```bash
docker compose exec api composer install
```

### 5️⃣ Міграції бази даних без входу в контейнер
```bash
docker compose exec api php bin/console doctrine:migrations:migrate
```

### 6️⃣ Створення root-користувача без входу в контейнер
```bash
docker compose exec api php bin/console app:user:create-root +380635492939 ]YZ5oY0m
```

```text
В базі даних буде створено root-користувача з такими даними:
id:        1
login:     root
password:  ]YZ5oY0m
phone:     +380635492939
```
### 🔐 Авторизація
API використовує **JWT токен** для авторизації користувачів.  
Всі запити до `/v1/api/*` повинні містити заголовок `Authorization` з токеном:

```html
Authorization: Bearer YOUR_ROOT_TOKEN
```

Замініть YOUR_ROOT_TOKEN на реальний токен root-користувача або будь-якого іншого користувача.

### 🧪 Приклади API-запитів (curl)
🔹 Отримання токена через login ендпоінт

Доданий ендпоінт для авторизації: /v1/api/login.
Він приймає `login` та `password` і у відповіді повертає JWT токен, який потім використовується у всіх запитах API.
```bash
curl --location --request POST 'http://localhost:8045/v1/api/login' \
--header 'Content-Type: application/json' \
--data '{
    "login": "test_user",
    "password": "123456"
}'

```

🔹 Створити користувача
```bash
curl --location --request POST 'http://localhost:8045/v1/api/users' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer YOUR_ROOT_TOKEN' \
--data '{
    "login": "test_user",
    "phone": "+380991112233",
    "password": "123456"
}'
```
🔹 Оновити користувача
```bash
curl --location --request PUT 'http://localhost:8045/v1/api/users' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer YOUR_ROOT_TOKEN' \
--data '{
    "id": "2",
    "login": "updated_user",
    "phone": "+380998877665",
    "password": "newpassword"
}'
```

🔹 Отримати користувача по ID
```bash
curl --location --request GET 'http://localhost:8045/v1/api/users/2' \
--header 'Authorization: Bearer YOUR_ROOT_TOKEN'
```

🔹 Видалити користувача
```bash
curl --location --request DELETE 'http://localhost:8045/v1/api/users/2' \
--header 'Authorization: Bearer YOUR_ROOT_TOKEN'
```

### 🧪 Postman Collection
```text
### 6️⃣ Postman Collection

Щоб швидко тестувати API:

1. Скопіюй JSON нижче у файл `users-api.postman_collection.json`
2. Відкрий Postman → **Import** → **File** → вибери цей файл
3. Замініть `YOUR_ROOT_TOKEN` на токен root-користувача
4. Тепер готові GET, POST, PUT, DELETE запити до API

{
  "info": {
    "name": "Users API App",
    "_postman_id": "users-api-collection",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Get Users",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer YOUR_ROOT_TOKEN"
          }
        ],
        "url": {
          "raw": "http://localhost:8045/v1/api/users/1",
          "protocol": "http",
          "host": ["localhost"],
          "port": "8045",
          "path": ["api","users","1"]
        }
      }
    },
    {
      "name": "Create User",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer YOUR_ROOT_TOKEN"
          },
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"login\": \"test_user\",\n  \"password\": \"123456\",\n  \"phone\": \"+380991112233\"\n}"
        },
        "url": {
          "raw": "http://localhost:8045/v1/api/users",
          "protocol": "http",
          "host": ["localhost"],
          "port": "8045",
          "path": ["api","users"]
        }
      }
    },
    {
      "name": "Update User",
      "request": {
        "method": "PUT",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer YOUR_ROOT_TOKEN"
          },
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"id\": \"user_id\", \n  \"login\": \"updated_user\",\n  \"password\": \"newpassword\",\n  \"phone\": \"+380998877665\"\n}"
        },
        "url": {
          "raw": "http://localhost:8045/v1/api/users",
          "protocol": "http",
          "host": ["localhost"],
          "port": "8045",
          "path": ["api","users"]
        }
      }
    },
    {
      "name": "Delete User",
      "request": {
        "method": "DELETE",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer YOUR_ROOT_TOKEN"
          }
        ],
        "url": {
          "raw": "http://localhost:8045/v1/api/users/1",
          "protocol": "http",
          "host": ["localhost"],
          "port": "8045",
          "path": ["api","users","1"]
        }
      }
    }
  ]
}
```
