### 🔑 General Rules

These are the general coding rules and guidelines to follow for any development task or interaction across the project:

---

#### 🔁 **General Development Behavior**

1. **Do not remove existing implementations** unless explicitly instructed **or** they conflict with the task being implemented.
2. **Always follow the structure, style, and conventions** from existing files in the codebase.
3. When adding new functionality, **mirror existing services, validators, resources, DTOs, and routes** for consistency.

#### ⚙️ **File & Folder Organization**

4. Always place **validation logic** in the `validators/` folder for reusability.
5. DTOs must be created under the `dtos/` folder and should contain necessary validation rules.
6. Business logic and services should always be located inside the `services/` folder and not in controllers.

#### 💡 **Adhere to Existing Conventions**

7. When adding new features or tasks, refer to **existing files** for structure and conventions. Copy and modify when necessary.

#### 🛠️ **Development Efficiency**

8. Create reusable components, methods, and services whenever possible to reduce duplication.
9. Always validate user inputs using DTO validation rules before any database interaction.

---

### 🔧 Setup, Configuration, and Dependencies

- Make sure to **use the shared database config** for connecting to the database using Drizzle ORM, ensuring proper query execution and connection pooling.
- Always use **naming conventions** that match the project’s existing structure.
