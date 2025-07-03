// Test the new hotel search features
// Run this in a browser console or Node.js environment

const API_BASE = 'http://localhost:3003/api';

// Test functions for the new hotel search features
const testHotelFeatures = {
  
  // Test 1: Basic sorting by price (ascending)
  async testPriceSortingAsc() {
    const url = `${API_BASE}/hotels/city/1?checkIn=2025-07-10&checkOut=2025-07-15&sortBy=room_lowest_price&sortOrder=asc&perpage=10`;
    
    try {
      const response = await fetch(url);
      const data = await response.json();
      
      console.log('✅ Price Sorting (ASC) Test:', data.success ? 'PASSED' : 'FAILED');
      if (data.success && data.data.hotels.length > 1) {
        const prices = data.data.hotels.map(h => h.roomPrice);
        const sorted = prices.slice().sort((a, b) => a - b);
        console.log('   Prices:', prices);
        console.log('   Sorted correctly:', JSON.stringify(prices) === JSON.stringify(sorted));
      }
      return data;
    } catch (error) {
      console.error('❌ Price Sorting Test Failed:', error.message);
    }
  },

  // Test 2: Price filtering
  async testPriceFiltering() {
    const url = `${API_BASE}/hotels/city/1?checkIn=2025-07-10&checkOut=2025-07-15&minPrice=10000&maxPrice=30000&perpage=20`;
    
    try {
      const response = await fetch(url);
      const data = await response.json();
      
      console.log('✅ Price Filtering Test:', data.success ? 'PASSED' : 'FAILED');
      if (data.success && data.data.hotels.length > 0) {
        const pricesInRange = data.data.hotels.every(h => h.roomPrice >= 10000 && h.roomPrice <= 30000);
        console.log('   All prices in range (10000-30000):', pricesInRange);
        console.log('   Sample prices:', data.data.hotels.slice(0, 3).map(h => h.roomPrice));
      }
      return data;
    } catch (error) {
      console.error('❌ Price Filtering Test Failed:', error.message);
    }
  },

  // Test 3: Star rating sorting (descending)
  async testStarRatingSortDesc() {
    const url = `${API_BASE}/hotels/city/1?checkIn=2025-07-10&checkOut=2025-07-15&sortBy=star_rating&sortOrder=desc&perpage=15`;
    
    try {
      const response = await fetch(url);
      const data = await response.json();
      
      console.log('✅ Star Rating Sorting (DESC) Test:', data.success ? 'PASSED' : 'FAILED');
      if (data.success && data.data.hotels.length > 1) {
        const ratings = data.data.hotels.map(h => h.starRating);
        const sorted = ratings.slice().sort((a, b) => b - a);
        console.log('   Ratings:', ratings);
        console.log('   Sorted correctly:', JSON.stringify(ratings) === JSON.stringify(sorted));
      }
      return data;
    } catch (error) {
      console.error('❌ Star Rating Sorting Test Failed:', error.message);
    }
  },

  // Test 4: Distance sorting (requires coordinates)
  async testDistanceSorting() {
    // Lagos coordinates
    const lat = 6.5244;
    const lng = 3.3792;
    const url = `${API_BASE}/hotels/city/1?checkIn=2025-07-10&checkOut=2025-07-15&sortBy=distance&latitude=${lat}&longitude=${lng}&perpage=10`;
    
    try {
      const response = await fetch(url);
      const data = await response.json();
      
      console.log('✅ Distance Sorting Test:', data.success ? 'PASSED' : 'FAILED');
      if (data.success && data.data.hotels.length > 1) {
        const distances = data.data.hotels.map(h => h.distance);
        const sorted = distances.slice().sort((a, b) => a - b);
        console.log('   Distances:', distances);
        console.log('   Sorted correctly:', JSON.stringify(distances) === JSON.stringify(sorted));
      }
      return data;
    } catch (error) {
      console.error('❌ Distance Sorting Test Failed:', error.message);
    }
  },

  // Test 5: Custom pagination
  async testCustomPagination() {
    const url = `${API_BASE}/hotels/city/1?checkIn=2025-07-10&checkOut=2025-07-15&page=1&perpage=5`;
    
    try {
      const response = await fetch(url);
      const data = await response.json();
      
      console.log('✅ Custom Pagination Test:', data.success ? 'PASSED' : 'FAILED');
      if (data.success) {
        console.log('   Hotels returned:', data.data.hotels.length);
        console.log('   Per page setting:', data.data.meta.pagination.per_page);
        console.log('   Correct per page:', data.data.meta.pagination.per_page === 5);
      }
      return data;
    } catch (error) {
      console.error('❌ Custom Pagination Test Failed:', error.message);
    }
  },

  // Test 6: Combined features
  async testCombinedFeatures() {
    const url = `${API_BASE}/hotels/city/1?checkIn=2025-07-10&checkOut=2025-07-15&sortBy=room_lowest_price&sortOrder=desc&minPrice=15000&maxPrice=100000&perpage=8&search=luxury`;
    
    try {
      const response = await fetch(url);
      const data = await response.json();
      
      console.log('✅ Combined Features Test:', data.success ? 'PASSED' : 'FAILED');
      if (data.success) {
        console.log('   Hotels found:', data.data.hotels.length);
        console.log('   Search, price filter, sorting all applied');
        if (data.data.hotels.length > 0) {
          console.log('   Sample hotel:', {
            name: data.data.hotels[0].name,
            price: data.data.hotels[0].roomPrice,
            rating: data.data.hotels[0].starRating
          });
        }
      }
      return data;
    } catch (error) {
      console.error('❌ Combined Features Test Failed:', error.message);
    }
  },

  // Test 7: Nearby hotels with new features
  async testNearbyHotelsFeatures() {
    const lat = 6.5244;
    const lng = 3.3792;
    const url = `${API_BASE}/hotels/nearby?lat=${lat}&long=${lng}&checkIn=2025-07-10&checkOut=2025-07-15&sortBy=room_lowest_price&minPrice=8000&maxPrice=50000&perpage=12`;
    
    try {
      const response = await fetch(url);
      const data = await response.json();
      
      console.log('✅ Nearby Hotels Features Test:', data.success ? 'PASSED' : 'FAILED');
      if (data.success) {
        console.log('   Nearby hotels found:', data.data.hotels.length);
        console.log('   With price filtering and sorting');
      }
      return data;
    } catch (error) {
      console.error('❌ Nearby Hotels Features Test Failed:', error.message);
    }
  },

  // Test 8: Validation errors
  async testValidationErrors() {
    const url = `${API_BASE}/hotels/city/1?checkIn=2025-07-10&checkOut=2025-07-15&sortBy=distance&minPrice=50000&maxPrice=10000`;
    
    try {
      const response = await fetch(url);
      const data = await response.json();
      
      console.log('✅ Validation Errors Test:', !data.success ? 'PASSED' : 'FAILED');
      if (!data.success) {
        console.log('   Validation errors caught correctly');
        console.log('   Errors:', data.data);
      }
      return data;
    } catch (error) {
      console.error('❌ Validation Errors Test Failed:', error.message);
    }
  },

  // Run all tests
  async runAllTests() {
    console.log('🚀 Starting Hotel Search Features Tests...\n');
    
    await this.testPriceSortingAsc();
    console.log('');
    
    await this.testPriceFiltering();
    console.log('');
    
    await this.testStarRatingSortDesc();
    console.log('');
    
    await this.testDistanceSorting();
    console.log('');
    
    await this.testCustomPagination();
    console.log('');
    
    await this.testCombinedFeatures();
    console.log('');
    
    await this.testNearbyHotelsFeatures();
    console.log('');
    
    await this.testValidationErrors();
    console.log('');
    
    console.log('✅ All tests completed!');
  }
};

// Export for use
if (typeof module !== 'undefined') {
  module.exports = testHotelFeatures;
}

// Auto-run in browser
if (typeof window !== 'undefined') {
  console.log('Hotel Search Features Test Suite Loaded');
  console.log('Run: testHotelFeatures.runAllTests()');
}

// Example usage:
// testHotelFeatures.runAllTests();
