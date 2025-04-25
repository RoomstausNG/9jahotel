import { env } from "../config/env";
export function getFullImageUrl(imagePath: string): string {
  if (!imagePath) return "";

  // If already a full URL, return as-is
  if (imagePath.startsWith("http")) {
    return imagePath;
  }

  // Remove leading slash if present
  const cleanPath = imagePath.startsWith("/") ? imagePath.slice(1) : imagePath;

  return `${this.CLOUDINARY_BASE_URL}/${cleanPath}`;
}
