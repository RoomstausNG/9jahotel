import { injectable } from "inversify";
import { eq, and } from "drizzle-orm";
import { db } from "../config/database";
import { smart_tvs, hotels, rooms } from "../database/migrations/schema";
import { ApiResponse } from "../utils/ApiResponse";

export interface TVRegistrationData {
  hotelcode: string;
  roomnumber: string;
  macaddress: string;
  ipaddress: string;
}

export interface CheckInData {
  hotelcode: string;
  roomnumber: string;
  message?: string;
  fullname: string;
  macaddress: string;
  tvid: string;
  checkindatetime: string;
  checkoutdatetime: string;
}

export interface ReminderData {
  hotelcode: string;
  roomnumber: string;
  reminderType: string;
  macaddress: string;
  checkoutdatetime: string;
  message?: string;
}

export interface CheckoutData {
  hotelcode: string;
  roomnumber: string;
  fullname: string;
  macaddress: string;
  checkoutdatetime: string;
  message?: string;
}

export interface BroadcastData {
  hotelcode: string;
  message: string;
}

@injectable()
export class TVService {
  /**
   * Sanitize MAC address to handle any format and return clean version
   */
  private sanitizeMacAddress(macAddress: string): string {
    // Remove all non-alphanumeric characters and convert to uppercase
    const clean = macAddress.replace(/[^a-zA-Z0-9]/g, "").toUpperCase();
    // Return the cleaned MAC address (typically 12 characters for standard MAC)
    return clean;
  }

  /**
   * Find hotel by code
   */
  async findHotelByCode(hotelcode: string) {
    const hotel = await db
      .select()
      .from(hotels)
      .where(eq(hotels.code, hotelcode))
      .limit(1);

    return hotel[0] || null;
  }

  /**
   * Find room by hotel ID and room number
   */
  async findRoomByNumber(hotelId: number, roomnumber: string) {
    const room = await db
      .select()
      .from(rooms)
      .where(and(eq(rooms.hotel_id, hotelId), eq(rooms.number, roomnumber)))
      .limit(1);

    return room[0] || null;
  }

  /**
   * Find TV by MAC address
   */
  async findTVByMacAddress(macAddress: string) {
    const tv = await db
      .select()
      .from(smart_tvs)
      .where(eq(smart_tvs.mac_address, macAddress))
      .limit(1);

    return tv[0] || null;
  }

  /**
   * Register or update a TV
   */
  async registerTV(data: TVRegistrationData) {
    const { hotelcode, roomnumber, macaddress, ipaddress } = data;

    // Sanitize MAC address
    const sanitizedMac = this.sanitizeMacAddress(macaddress);

    // Find hotel
    const hotel = await this.findHotelByCode(hotelcode);
    if (!hotel) {
      throw new Error("Hotel not found");
    }

    // Find room
    const room = await this.findRoomByNumber(hotel.id, roomnumber);
    if (!room) {
      throw new Error("Room not found");
    }

    // Check if TV already exists
    let tv = await this.findTVByMacAddress(sanitizedMac);

    // Use MySQL compatible datetime format (YYYY-MM-DD HH:mm:ss)
    const now = new Date().toISOString().slice(0, 19).replace("T", " ");

    let isNew = false;
    
    if (tv) {
      // Check if existing TV matches the current registration details
      if (tv.hotel_id === hotel.id && tv.room_id === room.id) {
        // ... (existing update logic)
          await db
          .update(smart_tvs)
          .set({
            last_seen_at: now,
             ip_address: ipaddress, // Update IP just in case
             status: 'active',
          })
          .where(eq(smart_tvs.id, tv.id));
      } else {
        // ... (existing update logic)
        await db
          .update(smart_tvs)
          .set({
            room_id: room.id,
            ip_address: ipaddress,
            hotel_id: hotel.id,
            last_seen_at: now,
            updated_at: now,
            status: 'active',
          })
          .where(eq(smart_tvs.id, tv.id));
      }
    } else {
      isNew = true;
      // Create new TV
      const [result] = await db
        .insert(smart_tvs)
        .values({
          room_id: room.id,
          name: `TV-${room.number}`,
          ip_address: ipaddress,
          mac_address: sanitizedMac,
          hotel_id: hotel.id,
          last_seen_at: now,
          created_at: now,
          updated_at: now,
          status: 'active',
        });

      // Fetch the created TV
      const newTv = await db
        .select()
        .from(smart_tvs)
        .where(eq(smart_tvs.id, result.insertId))
        .limit(1);

      tv = newTv[0];
    }

    return {
      tv,
      hotel,
      room,
      sanitizedMac,
      isNew,
    };
  }

  /**
   * Update TV last seen and return TV ID
   */
  async checkIn(data: CheckInData) {
    const { macaddress } = data;

    const sanitizedMac = this.sanitizeMacAddress(macaddress);

    // Find TV first to get its ID
    const tv = await this.findTVByMacAddress(sanitizedMac);
    if (!tv) {
      throw new Error("TV not found");
    }

    // Update last seen
    await db
      .update(smart_tvs)
      .set({
        last_seen_at: new Date().toISOString().slice(0, 19).replace("T", " "),
      })
      .where(eq(smart_tvs.id, tv.id));

    return { tvId: tv.id, sanitizedMac };
  }

  /**
   * Update TV last seen and return TV ID
   */
  async checkOut(data: CheckoutData) {
    const { macaddress } = data;

    const sanitizedMac = this.sanitizeMacAddress(macaddress);

    // Find TV first to get its ID
    const tv = await this.findTVByMacAddress(sanitizedMac);
    if (!tv) {
      throw new Error("TV not found");
    }

    // Update last seen
    await db
      .update(smart_tvs)
      .set({
        last_seen_at: new Date().toISOString().slice(0, 19).replace("T", " "),
      })
      .where(eq(smart_tvs.id, tv.id));

    return { tvId: tv.id, sanitizedMac };
  }

  /**
   * Update TV last seen and return TV ID
   */
  async getTVForBroadcast(data: ReminderData) {
    const { macaddress } = data;

    const sanitizedMac = this.sanitizeMacAddress(macaddress);

    // Find TV first to get its ID
    const tv = await this.findTVByMacAddress(sanitizedMac);
    if (!tv) {
      throw new Error("TV not found");
    }

    // Update last seen
    await db
      .update(smart_tvs)
      .set({
        last_seen_at: new Date().toISOString().slice(0, 19).replace("T", " "),
      })
      .where(eq(smart_tvs.id, tv.id));

    return { tvId: tv.id, sanitizedMac };
  }

  /**
   * Get all TVs in a hotel for broadcast
   */
  async getHotelTVs(hotelCode: string) {
    const hotel = await this.findHotelByCode(hotelCode);
    if (!hotel) {
      throw new Error("Hotel not found");
    }

    const tvs = await db
      .select()
      .from(smart_tvs)
      .where(eq(smart_tvs.hotel_id, hotel.id));

    return tvs;
  }
}
