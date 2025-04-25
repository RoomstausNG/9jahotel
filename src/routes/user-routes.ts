import { Hono } from "hono";
import { container } from "../container";
import { UserController } from "../controllers/UserController";
import { AuthController } from "../controllers/AuthController";
import { zValidator } from "../validators/validator-wrapper";
import { authMiddleware } from "../middlewares/auth-middleware";
import { CreateUserDto } from "../dtos/UserDTO";

const userRoutes = new Hono();
const userController = container.get(UserController);
const authController = container.get(AuthController);

// Auth routes
userRoutes.post("/register", (c) => authController.register(c));
userRoutes.post("/login", (c) => authController.login(c));

// Protected user routes
userRoutes.get("/", authMiddleware, (c) => userController.index(c));
userRoutes.get("/:id", authMiddleware, (c) => userController.show(c));
userRoutes.post("/", zValidator("json", CreateUserDto), (c) =>
  userController.store(c)
);

export default userRoutes;
