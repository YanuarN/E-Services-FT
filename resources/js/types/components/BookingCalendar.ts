import type { BookingDayAvailability } from '@/types/Booking';

export type BookingCalendarProps = {
  selectedDate?: Date;
  month: Date;
  onSelectDate: (date: Date | undefined) => void;
  onMonthChange: (month: Date) => void;
  availabilityMap: Record<string, BookingDayAvailability>;
  isRoomSelected: boolean;
  selectedRoomLabel?: string;
};
