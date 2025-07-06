// Test the updated booking functionality
// Run this in a browser console or Node.js environment

const API_BASE = "http://localhost:3000/api";

const testBookingUpdates = {
  // Test 1: Create booking with new customer (should create new customer)
  async testNewCustomerBooking() {
    console.log("1. Testing booking with new customer...");

    const bookingData = {
      checkIn: "2025-07-10",
      checkOut: "2025-07-15",
      firstname: "John",
      lastname: "Doe",
      email: "john.doe.new@test.com",
      phone: "1234567890",
      roomIds: [1], // Adjust based on your room IDs
      type: "PayAtHotel",
      total: 50000,
    };

    try {
      const response = await fetch(`${API_BASE}/bookings`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(bookingData),
      });

      const data = await response.json();

      console.log(
        "   New customer booking result:",
        data.success ? "SUCCESS" : "FAILED"
      );
      if (data.success) {
        console.log("   ✅ Booking created for new customer");
        console.log("   Booking reference:", data.data?.reference);
      } else {
        console.log("   ❌ Failed:", data.message);
        console.log("   Errors:", data.data);
      }

      return data;
    } catch (error) {
      console.error("   ❌ Request error:", error.message);
      return null;
    }
  },

  // Test 2: Create booking with existing customer (should find existing customer)
  async testExistingCustomerBooking() {
    console.log("2. Testing booking with existing customer...");

    const bookingData = {
      checkIn: "2025-07-12",
      checkOut: "2025-07-17",
      firstname: "John",
      lastname: "Doe",
      email: "john.doe.new@test.com", // Same email as test 1
      phone: "1234567890", // Same phone as test 1
      roomIds: [1], // Adjust based on your room IDs
      type: "PayNow",
      total: 60000,
    };

    try {
      const response = await fetch(`${API_BASE}/bookings`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(bookingData),
      });

      const data = await response.json();

      console.log(
        "   Existing customer booking result:",
        data.success ? "SUCCESS" : "FAILED"
      );
      if (data.success) {
        console.log("   ✅ Booking created for existing customer");
        console.log("   Booking reference:", data.data?.reference);
      } else {
        console.log("   ❌ Failed:", data.message);
        console.log("   Errors:", data.data);
      }

      return data;
    } catch (error) {
      console.error("   ❌ Request error:", error.message);
      return null;
    }
  },

  // Test 3: Test validation still works for required fields
  async testValidation() {
    console.log("3. Testing validation...");

    const invalidBookingData = {
      checkIn: "2025-07-10",
      checkOut: "2025-07-15",
      firstname: "Jo", // Too short
      lastname: "Do", // Too short
      email: "invalid-email", // Invalid email
      phone: "123", // Too short
      roomIds: [999999], // Invalid room ID
      type: "InvalidType", // Invalid type
      total: -100, // Negative total
    };

    try {
      const response = await fetch(`${API_BASE}/bookings`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(invalidBookingData),
      });

      const data = await response.json();

      console.log(
        "   Validation test result:",
        !data.success ? "SUCCESS" : "FAILED"
      );
      if (!data.success) {
        console.log("   ✅ Validation correctly rejected invalid data");
        console.log("   Validation errors:", data.data);
      } else {
        console.log("   ❌ Validation should have failed but didn't");
      }

      return data;
    } catch (error) {
      console.error("   ❌ Request error:", error.message);
      return null;
    }
  },

  // Test 4: Test booking with phone number match (different email, same phone)
  async testPhoneMatchBooking() {
    console.log("4. Testing booking with phone number match...");

    const bookingData = {
      checkIn: "2025-07-14",
      checkOut: "2025-07-19",
      firstname: "Jane",
      lastname: "Smith",
      email: "jane.smith@different.com", // Different email
      phone: "1234567890", // Same phone as previous tests
      roomIds: [1],
      type: "NonRefundable",
      total: 40000,
    };

    try {
      const response = await fetch(`${API_BASE}/bookings`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(bookingData),
      });

      const data = await response.json();

      console.log(
        "   Phone match booking result:",
        data.success ? "SUCCESS" : "FAILED"
      );
      if (data.success) {
        console.log("   ✅ Booking created using phone number match");
        console.log("   Booking reference:", data.data?.reference);
      } else {
        console.log("   ❌ Failed:", data.message);
        console.log("   Errors:", data.data);
      }

      return data;
    } catch (error) {
      console.error("   ❌ Request error:", error.message);
      return null;
    }
  },

  // Run all tests
  async runAllTests() {
    console.log("🧪 Testing Updated Booking Functionality\n");

    await this.testNewCustomerBooking();
    console.log("");

    await this.testExistingCustomerBooking();
    console.log("");

    await this.testValidation();
    console.log("");

    await this.testPhoneMatchBooking();
    console.log("");

    console.log("✅ All booking tests completed!");
    console.log("\nSummary:");
    console.log("- Removed unique email/phone validation from BookingDTO");
    console.log("- Updated booking process to find existing customers");
    console.log("- Existing customers can now make multiple bookings");
    console.log("- New customers are still created when needed");
  },
};

// Export for use
if (typeof module !== "undefined") {
  module.exports = testBookingUpdates;
}

// Auto-run in browser
if (typeof window !== "undefined") {
  console.log("Booking Updates Test Suite Loaded");
  console.log("Run: testBookingUpdates.runAllTests()");
}

// Example usage:
// testBookingUpdates.runAllTests();
