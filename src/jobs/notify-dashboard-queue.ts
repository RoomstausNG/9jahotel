// src/jobs/notify-dashboard-queue.ts
import axios from "axios";

/**
 * Queue a job to notify 9jahotel dashboard of a successful booking
 * @param notificationData - The booking notification payload
 */
export async function queueDashboardNotificationJob(notificationData: any) {
  // Simulate queue: setTimeout for delayed execution (replace with real queue in production)
  setTimeout(async () => {
    try {
      const response = await axios.post(
        "https://dashboard.9jahotel.com/api/v1_hotel/bookings/notify",
        notificationData,
        {
          headers: {
            "Content-Type": "application/json",
          },
          timeout: 20000, // Increased timeout to 20 seconds
        }
      );
      if (response.status >= 200 && response.status < 300) {
        console.log(
          `✅ Successfully notified 9jahotel dashboard for booking ${notificationData.booking_id}`
        );
      } else {
        console.error(
          `❌ Failed to notify 9jahotel dashboard for booking ${notificationData.booking_id}. Status: ${response.status}`
        );
      }
    } catch (notificationError) {
      console.error("Failed to notify 9jahotel dashboard:", notificationError);
    }
  }, 0); // Immediate queue, replace with real job delay if needed
}
