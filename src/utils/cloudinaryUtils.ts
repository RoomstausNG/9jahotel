import { UploadApiErrorResponse, UploadApiResponse } from "cloudinary";
import cloudinary from "../config/cloudinary";
import { env } from "../config/env";
import { Readable } from "stream"; // Import Readable if using streams

// Helper function to convert buffer to readable stream if needed by upload_stream
export function bufferToStream(buffer: Buffer): Readable {
  const stream = new Readable();
  stream.push(buffer);
  stream.push(null); // Signal end of stream
  return stream;
}

export function getFullImageUrl(imagePath: string): string {
  if (!imagePath) return `${env("APP_URL")}/img/logo.png`;

  // If already a full URL, return as-is
  if (imagePath.startsWith("http")) {
    return imagePath;
  }

  // Remove leading slash if present
  const cleanPath = imagePath.startsWith("/") ? imagePath.slice(1) : imagePath;

  // Ensure the path has an extension
  const hasExtension = cleanPath.includes(".");
  const finalPath = hasExtension ? cleanPath : `${cleanPath}.webp`;

  return `https://res.cloudinary.com/${env(
    "CLOUDINARY_CLOUD_NAME"
  )}/image/upload/${finalPath}`;
}

export function getCloudinaryImage(publicId: string): string {
  try {
    if (publicId && typeof publicId === "string") {
      // Remove file extension using regex
      const cleanPublicId = publicId.replace(/\.(jpg|jpeg|png|webp|gif)$/i, "");

      // Ensure we have a valid public ID after cleaning
      if (cleanPublicId) {
        // Manual Cloudinary URL construction
        const cloudinaryUrl = `https://res.cloudinary.com/${env(
          "CLOUDINARY_CLOUD_NAME"
        )}/image/upload/${cleanPublicId}.webp`;

        // Check if we got a valid URL back
        if (cloudinaryUrl) {
          return cloudinaryUrl;
        }
      }
    }
  } catch (error) {
    console.error("Cloudinary image error:", error, {
      publicId,
    });
  }

  // Return default avatar if anything goes wrong
  return `${env("APP_URL")}/img/logo.png`; // https://test.9jahotel.com/img/logo.png
}

/**
 * Uploads an image buffer to Cloudinary with specified transformations.
 * @param fileBuffer The image data as a Buffer.
 * @param options Optional parameters like folder and public_id.
 * @returns Promise resolving to the Cloudinary upload response.
 */
export async function uploadAndTransformImage(
  fileBuffer: Buffer,
  options: { folder?: string; public_id?: string } = {}
): Promise<UploadApiResponse> {
  return new Promise((resolve, reject) => {
    const uploadOptions = {
      folder: options.folder,
      public_id: options.public_id,
      overwrite: true,
      transformation: [
        { gravity: "face", height: 300, width: 300, crop: "thumb" },
        { radius: 1000 },
        { overlay: { resource_type: "image", public_id: "logo_kbhbqo" } },
        // {
        //   overlay: {
        //     text: "9jahotel.com", // Your desired text
        //     font_family: "Arial", // Choose a font
        //     font_size: 30, // Adjust size as needed
        //     color: "white",
        //     font_weight: "bold",
        //   },
        // },
        {
          flags: "layer_apply",
          gravity: "south_east", // eg south_east
          x: 10,
          y: 10,
          width: 50,
        },
        // { effect: "brightness:90" },
        { opacity: 100 },
        // { angle: 10 },
        { quality: "auto" },
        { fetch_format: "webp" },
      ],
    };

    const uploadStream = cloudinary.uploader.upload_stream(
      uploadOptions,
      (
        error: UploadApiErrorResponse | undefined,
        result: UploadApiResponse | undefined
      ) => {
        if (error) {
          console.error("Cloudinary Upload Error:", error);
          return reject(
            new Error(`Cloudinary upload failed: ${error.message}`)
          );
        }
        if (!result) {
          return reject(
            new Error("Cloudinary upload failed: No result returned.")
          );
        }
        console.log("Cloudinary Upload Success:", result.public_id);
        resolve(result);
      }
    );

    // Convert buffer to stream and pipe it to Cloudinary
    // bufferToStream(fileBuffer).pipe(uploadStream);
    uploadStream.end(fileBuffer);
  });
}

/**
 * Generates a Cloudinary upload signature
 * @param params Parameters to include in the signature (e.g., folder, public_id)
 * @returns { signature: string, timestamp: number }
 */
export function generateCloudinarySignature(params: Record<string, any> = {}): {
  signature: string;
  timestamp: number;
} {
  const timestamp = Math.floor(Date.now() / 1000);
  const apiSecret = env("CLOUDINARY_API_SECRET");

  // Sort params alphabetically
  const sortedParams = Object.keys(params)
    .sort()
    .map((key) => `${key}=${params[key]}`)
    .join("&");

  const stringToSign = sortedParams
    ? `${sortedParams}&timestamp=${timestamp}${apiSecret}`
    : `timestamp=${timestamp}${apiSecret}`;

  // Use crypto to create SHA-1 hash
  const crypto = require("crypto");
  const signature = crypto
    .createHash("sha1")
    .update(stringToSign)
    .digest("hex");

  return { signature, timestamp };
}
