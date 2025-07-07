// Test script to verify optional check-in/check-out dates for nearby hotels
import { NearbyHotelDTO } from "./src/dtos/LocationDTO";

async function testNearbyHotelDTO() {
  console.log(
    "Testing NearbyHotelDTO with optional check-in/check-out dates...\n"
  );

  // Test 1: No dates provided (should pass)
  try {
    const result1 = await NearbyHotelDTO.parseAsync({
      lat: 12.9716,
      long: 77.5946,
      page: 1,
      perpage: 10,
      search: "test",
    });
    console.log("✅ Test 1 PASSED: No dates provided");
  } catch (error) {
    console.log("❌ Test 1 FAILED: No dates provided");
    console.log("Error:", error.message);
  }

  // Test 2: Both dates provided (should pass)
  try {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dayAfter = new Date();
    dayAfter.setDate(dayAfter.getDate() + 2);

    const result2 = await NearbyHotelDTO.parseAsync({
      lat: 12.9716,
      long: 77.5946,
      checkIn: tomorrow.toISOString(),
      checkOut: dayAfter.toISOString(),
      page: 1,
      perpage: 10,
      search: "test",
    });
    console.log("✅ Test 2 PASSED: Both dates provided");
  } catch (error) {
    console.log("❌ Test 2 FAILED: Both dates provided");
    console.log("Error:", error.message);
  }

  // Test 3: Only check-in provided (should fail)
  try {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);

    const result3 = await NearbyHotelDTO.parseAsync({
      lat: 12.9716,
      long: 77.5946,
      checkIn: tomorrow.toISOString(),
      page: 1,
      perpage: 10,
      search: "test",
    });
    console.log(
      "❌ Test 3 FAILED: Only check-in provided (should have failed)"
    );
  } catch (error) {
    console.log("✅ Test 3 PASSED: Only check-in provided correctly rejected");
  }

  // Test 4: Only check-out provided (should fail)
  try {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);

    const result4 = await NearbyHotelDTO.parseAsync({
      lat: 12.9716,
      long: 77.5946,
      checkOut: tomorrow.toISOString(),
      page: 1,
      perpage: 10,
      search: "test",
    });
    console.log(
      "❌ Test 4 FAILED: Only check-out provided (should have failed)"
    );
  } catch (error) {
    console.log("✅ Test 4 PASSED: Only check-out provided correctly rejected");
  }

  console.log("\nAll tests completed!");
}

testNearbyHotelDTO().catch(console.error);
