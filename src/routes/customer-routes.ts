import { Hono } from "hono";
import { container } from "../container";
import { CustomerController } from "../controllers/CustomerController";

const customerRoutes = new Hono();

// Get CustomerController instance from container
const customerController =
  container.get<CustomerController>(CustomerController);

// Public routes (not guarded by auth middleware)
customerRoutes.post("/check-exists", (c) =>
  customerController.checkCustomerExists(c)
);
customerRoutes.get("/find-by-email", (c) => customerController.findByEmail(c));
customerRoutes.get("/find-by-phone", (c) => customerController.findByPhone(c));

// Protected routes (if needed in the future)
customerRoutes.get("/:id", (c) => customerController.show(c));

export { customerRoutes };
