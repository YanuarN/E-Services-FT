import type { BookingCalendarEvent, BookingRoom } from '@/types/Booking';

export type RoomBookingProps = {
  rooms: BookingRoom[];
  studyPrograms: string[];
};

export type RoomBookingSharedPageProps = {
  flash?: {
    success?: string;
    whatsappUrl?: string | null;
  };
  roomBookings?: BookingCalendarEvent[];
};
