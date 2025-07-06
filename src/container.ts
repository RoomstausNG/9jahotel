import { Container } from "inversify";
import { UserService } from "./services/UserService";
import { UserController } from "./controllers/UserController";
import { AuthController } from "./controllers/AuthController";
import { AuthService } from "./services/AuthService";
import { EmailService } from "./services/EmailService";
import { LocationController } from "./controllers/LocationController";
import { LocationService } from "./services/LocationService";
import { HotelService } from "./services/HotelService";
import { HotelController } from "./controllers/HotelController";
import { BookingService } from "./services/BookingService";
import { PaymentService } from "./services/PaymentService";
import { BookingController } from "./controllers/BookingController";
import { CustomerService } from "./services/CustomerService";
import { CustomerController } from "./controllers/CustomerController";

// Create the container
const container = new Container();

// Bind services
container.bind<UserService>(UserService).toSelf().inSingletonScope();
container.bind<AuthService>(AuthService).toSelf().inSingletonScope();
container.bind<EmailService>(EmailService).toSelf().inSingletonScope();
container.bind<LocationService>(LocationService).toSelf().inSingletonScope();
container.bind<HotelService>(HotelService).toSelf().inSingletonScope();
container.bind<BookingService>(BookingService).toSelf().inSingletonScope();
container.bind<PaymentService>(PaymentService).toSelf().inSingletonScope();
container.bind<CustomerService>(CustomerService).toSelf().inSingletonScope();

// Bind controllers
container.bind<UserController>(UserController).toSelf().inSingletonScope();
container.bind<AuthController>(AuthController).toSelf().inSingletonScope();
container
  .bind<LocationController>(LocationController)
  .toSelf()
  .inSingletonScope();
container.bind<HotelController>(HotelController).toSelf().inSingletonScope(); // Bind HotelController
container
  .bind<BookingController>(BookingController)
  .toSelf()
  .inSingletonScope();
container.bind<CustomerController>(CustomerController).toSelf().inSingletonScope();

export { container };
