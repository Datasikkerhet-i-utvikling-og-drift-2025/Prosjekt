# API Reference

## Get All Users

**Endpoint:**  
GET /api/index.php?route=users

**Description:**  
Returns a list of all users.

**Sample Request:**
```bash
curl -X GET http://localhost:5000/api/index.php?route=users
```

Sample Response:

```json
[
  {
    "user_id": 1,
    "email": "sample@user.com",
    "user_type": "student",
    "created_at": "2025-01-19 05:19:24"
  }
]
```


