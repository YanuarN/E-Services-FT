import type { BookingCalendarEvent, BookingRoom } from '@/types/Booking';

export type RoomBookingProps = {
  rooms: BookingRoom[];
  bookings: BookingCalendarEvent[];
  studyPrograms: string[];
};

export type RoomBookingSharedPageProps = {
  flash?: {
    success?: string;
  };
};
