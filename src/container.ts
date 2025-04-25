import { Container } from "inversify";
import { UserService } from "./services/UserService";
import { UserController } from "./controllers/UserController";
import { AuthController } from "./controllers/AuthController";
import { AuthService } from "./services/AuthService";
import { EmailService } from "./services/EmailService"; // Import EmailService
import { LocationController } from "./controllers/LocationController";
import { LocationService } from "./services/LocationService";
import { HotelService } from "./services/HotelService"; // Import HotelService
import { HotelController } from "./controllers/HotelController"; // Import HotelController

// Create the container
const container = new Container();

// Bind services
container.bind<UserService>(UserService).toSelf().inSingletonScope();
container.bind<AuthService>(AuthService).toSelf().inSingletonScope();
container.bind<EmailService>(EmailService).toSelf().inSingletonScope();
container.bind<LocationService>(LocationService).toSelf().inSingletonScope();
container.bind<HotelService>(HotelService).toSelf().inSingletonScope(); // Bind HotelService

// Bind controllers
container.bind<UserController>(UserController).toSelf().inSingletonScope();
container.bind<AuthController>(AuthController).toSelf().inSingletonScope();
container
  .bind<LocationController>(LocationController)
  .toSelf()
  .inSingletonScope();
container.bind<HotelController>(HotelController).toSelf().inSingletonScope(); // Bind HotelController

export { container };
