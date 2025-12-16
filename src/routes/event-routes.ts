import { Hono } from "hono";
import { container } from "../container";
import { EventController } from "../controllers/EventController";
import { zValidator } from "../validators/validator-wrapper";
import {
  TVRegistrationDTO,
  CheckInDTO,
  ReminderDTO,
  CheckoutDTO,
  BroadcastAllDTO,
} from "../dtos/EventDTO";

const eventRoutes = new Hono();
const eventController = container.get(EventController);

// TV Registration endpoint (unprotected)
eventRoutes.post(
  "/tv-registration",
  zValidator("json", TVRegistrationDTO),
  (c) => eventController.registerTV(c),
);

// Check-in event endpoint (unprotected)
eventRoutes.post("/check-in", zValidator("json", CheckInDTO), (c) =>
  eventController.checkIn(c),
);

// Reminder event endpoint (unprotected)
eventRoutes.post("/reminder", zValidator("json", ReminderDTO), (c) =>
  eventController.sendReminder(c),
);

// Checkout event endpoint (unprotected)
eventRoutes.post("/checkout", zValidator("json", CheckoutDTO), (c) =>
  eventController.checkOut(c),
);

// Broadcast all endpoint (unprotected)
eventRoutes.post("/broadcast-all", zValidator("json", BroadcastAllDTO), (c) =>
  eventController.broadcastAll(c),
);

export default eventRoutes;
