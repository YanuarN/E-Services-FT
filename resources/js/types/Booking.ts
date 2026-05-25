export type BookingStatus = 'APPROVED' | 'PENDING' | 'REJECTED';

export type BookingCalendarEvent = {
  id: number;
  requestId?: number | null;
  roomId: number | null;
  roomName: string;
  studentName: string;
  activityName: string;
  unit: string;
  start: string;
  end: string;
  status: BookingStatus;
};

export type BookingDayAvailability = {
  date: string;
  count: number;
  status: 'available' | 'partial' | 'full';
};

export type BookingRoom = {
  id: number;
  name: string;
  capacity: number;
};
