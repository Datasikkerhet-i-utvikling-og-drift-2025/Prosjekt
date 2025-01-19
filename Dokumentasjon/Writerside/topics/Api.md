# Api

## UserController

### Overview

The `UsersController` class handles user-related API operations, specifically:

- `GET getAllUsers()`: Retrieve all users from the database.
- `POST createUser()`: Create a new user in the database.

---

### Method: `GET getAllUsers()`

#### Endpoint: `GET /api/index.php?route=users`
This endpoint retrieves all users from the `users` table and returns the data in JSON format.

#### Response Format:
The response is a JSON-encoded array of users, where each user is represented by the following keys:
- `user_id` (string): The user's unique ID.
- `email` (string): The user's email address.
- `user_type` (string): The user's type (e.g., 'student', 'admin').
- `created_at` (string): The date and time when the user was created in the database.

#### Example Response:
```json
[
    {
        "user_id": "1",
        "email": "tony@stark.com",
        "user_type": "student",
        "created_at": "2025-01-19 12:51:46"
    }
]
```

------------------------------------------------------------------------------------------------------------------------

### Method: `POST createUser()`

#### Endpoint: `POST /api/index.php?route=users`

#### Parameters:
- `email` (string): The user's email address (e.g., `tony@stark.com`).
- `password` (string): The user's password (e.g., `strongpassword123`).
- `user_type` (string): The type of user (e.g., `admin`, `user`).

#### Request Body Example:
```json
{
    "email": "tony@stark.com",
    "password": "strongpassword123",
    "user_type": "admin"
}
```
#### Response:

##### Example Success Response:
```json
{
    "status": "success",
    "message": "User created successfully",
    "user": {
        "email": "tony@stark.com",
        "user_type": "admin"
    }
}

```

##### Example Error Response:
```json
{
    "error": "Missing required fields: email, password, user_type"
}

```









<!--

### Method: ``

#### Endpoint: ``


#### Response Format:

#### Example Response:
```json

```

#### Parameters:

#### Request Body Example:
```json

```

#### Response:

##### Example Success Response:
```json

```

##### Example Error Response:
```json

```


-->
