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
    let roomUpdated = false;
    
    if (tv) {
      // TV exists - check if room assignment changed
      if (tv.hotel_id === hotel.id && tv.room_id === room.id) {
        // Same TV, same room - just update last seen and IP
        console.log(`✓ TV reconnection: MAC=${sanitizedMac}, Room=${roomnumber}, Hotel=${hotelcode}, IP=${ipaddress}`);
        
        await db
          .update(smart_tvs)
          .set({
            last_seen_at: now,
            ip_address: ipaddress, // Update IP just in case
            status: 'active',
          })
          .where(eq(smart_tvs.id, tv.id));

        // Refresh TV data
        const updatedTv = await this.findTVByMacAddress(sanitizedMac);
        tv = updatedTv || tv;
      } else {
        // Same MAC but different room - update room assignment
        roomUpdated = true;
        
        // Get old room number for logging
        const oldRoom = await db
          .select()
          .from(rooms)
          .where(eq(rooms.id, tv.room_id))
          .limit(1);
        
        console.log(`✓ TV room updated: MAC=${sanitizedMac}, From Room=${oldRoom[0]?.number || 'unknown'} to Room=${roomnumber}, Hotel=${hotelcode}, IP=${ipaddress}`);
        
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

        // Refresh TV data
        const updatedTv = await this.findTVByMacAddress(sanitizedMac);
        tv = updatedTv || tv;
      }
    } else {
      // New TV - create it
      isNew = true;
      // New TV - create it
      isNew = true;
      
      // Calculate next sequential name (TV1-101, TV2-101, etc.)
      const existingTVs = await db
        .select()
        .from(smart_tvs)
        .where(eq(smart_tvs.room_id, room.id));

      let index = 1;
      let tvName = `TV${index}-${room.number}`;
      
      // Find first available index
      while (existingTVs.some(tv => tv.name === tvName)) {
        index++;
        tvName = `TV${index}-${room.number}`;
      }
      
      console.log(`✓ New TV created: Name=${tvName}, MAC=${sanitizedMac}, Room=${roomnumber}, Hotel=${hotelcode}, IP=${ipaddress}`);
      
      const [result] = await db
        .insert(smart_tvs)
        .values({
          room_id: room.id,
          name: tvName,
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
      roomUpdated,
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
  /**
   * Delete a TV
   */
  async deleteTV(macAddress: string, hotelCode: string) {
    const sanitizedMac = this.sanitizeMacAddress(macAddress);

    // Find hotel first
    const hotel = await this.findHotelByCode(hotelCode);
    if (!hotel) {
      throw new Error("Hotel not found");
    }

    // Find TV
    const tv = await this.findTVByMacAddress(sanitizedMac);
    if (!tv) {
      throw new Error("TV not found");
    }

    // Verify TV belongs to this hotel
    if (tv.hotel_id !== hotel.id) {
      throw new Error("TV does not belong to this hotel");
    }

    // Delete TV
    await db.delete(smart_tvs).where(eq(smart_tvs.id, tv.id));

    return { success: true, mac: sanitizedMac };
  }
}
