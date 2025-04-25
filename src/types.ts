export const TYPES = {
  Database: Symbol.for("Database"),
};

// Define the MySqlDatabase interface to match your database operations
export interface MySqlDatabase {
  select: Function;
  from: Function;
  limit: Function;
  offset: Function;
  where: Function;
  insert: Function;
  values: Function;
  returning: Function;
  fn: {
    count: Function;
  };
}
