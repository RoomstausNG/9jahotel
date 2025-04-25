import type { ApiResponse as ApiResponseType } from "../types/api";

export class ApiResponse {
  static success<T>(data: T, message: string = "Success", status = 200) {
    return Response.json(
      {
        success: true,
        message,
        data,
      } as ApiResponseType<T>,
      { status }
    );
  }

  static failure(
    message: string = "Something went wrong",
    status = 400,
    errors: any = null
  ) {
    // Create the base response object
    const responseBody: ApiResponseType<null> = {
      success: false,
      message,
    };

    // Only add errors to the response if it's not null
    if (errors !== null) {
      responseBody.errors = errors;
    }

    return Response.json(responseBody, { status });
  }
}
