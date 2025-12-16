import http from "k6/http";
import { check, sleep } from "k6";

const BASE_URL = "http://localhost:3006/api";

export let options = {
  stages: [
    { duration: "1m", target: 100 }, // Ramp-up to 100 users in 1 minute
    { duration: "2m", target: 200 }, // Ramp-up to 200 users in 2 minutes
    { duration: "3m", target: 300 }, // Ramp-up to 300 users in 3 minutes
    { duration: "5m", target: 500 }, // Ramp-up to 500 users in 5 minutes
    { duration: "10m", target: 1000 }, // Ramp-up to 1000 users in 10 minutes
    { duration: "5m", target: 2000 }, // Ramp-up to 1000 users in 10 minutes
    { duration: "3m", target: 5000 }, // Ramp-up to 1000 users in 10 minutes
    // { duration: "2m", target: 10000 }, // Ramp-up to 1000 users in 10 minutes
    { duration: "1m", target: 100 }, // Ramp-down to 500 users in 3 minutes
    { duration: ".1m", target: 0 }, // Cool down in 1 minute
  ],
};

export default function () {
  let response = http.get(`${BASE_URL}/users/13`);

  check(response, {
    "status is 200": (r) => r.status === 200,
    "response time is < 2s": (r) => r.timings.duration < 2000,
  });

  // Simulate a random delay between requests
  sleep(Math.random() * 3);
}
