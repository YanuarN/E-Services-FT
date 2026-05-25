import type { BookingCalendarEvent, BookingRoom } from '@/types/Booking';

export type RoomBookingProps = {
  rooms: BookingRoom[];
  studyPrograms: string[];
};

export type RoomBookingSharedPageProps = {
  flash?: {
    success?: string;
    whatsappUrl?: string | null;
    roomBookingConflicts?: {
      room_id: number;
      room_name: string;
      start_time: string;
      end_time: string;
    }[];
  };
  roomBookings?: BookingCalendarEvent[];
};
